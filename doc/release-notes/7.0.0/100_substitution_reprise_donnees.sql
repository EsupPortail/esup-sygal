--
-- Substitutions : reprise de données
--

--================================================== STRUCTURE ===================================================-

--
-- Migration des substitutions de structures pré-existantes.
-- ATTENTION : valable uniquement si aucune substitution automatique n'a été faite !
--
-- Principe :
--   - Alimentation des NPD dans les substitutions existantes (table 'substit_structure') ;
--   - Renseignement du NPD forcés dans les 'structure' où c'est nécessaire.
--
create or replace function tmp__substit_update__migrate_structure_substit() returns void
    language plpgsql
as
$$declare
    v_data record;
    v_structure structure;
    v_substit_structure substit_structure;
begin
    -- parcours des structures substituantes (pour chacune, on détermine le NPD majoritaire à partir des substituées).
    for v_data in select ss.to_id, mode() within group (order by substit_npd_structure(ps.*)) as best_npd -- NPD majoritaire
                  from substit_structure ss join structure ps on ss.from_id = ps.id
                  group by ss.to_id
        loop
            -- Là où il n'est pas renseigné, update du NPD de la substitution avec le NPD majoritaire.
            update substit_structure set npd = v_data.best_npd,
                                         histo_modification = current_timestamp,
                                         histo_modificateur_id = app_utilisateur_id()
                                     where to_id = v_data.to_id
                                       and npd is null;
            -- Pour pérenniser la substitution, on met le NPD majoritaire comme NPD forcé dans chaque substitué, ssi :
            --   - le substitué n'a pas déjà un NPD forcé ;
            --   - le NPD proposé diffère du NPD calculable par défaut.
            for v_substit_structure in select * from substit_structure where to_id = v_data.to_id loop
                update structure ps set npd_force = v_data.best_npd
                                     where ps.id = v_substit_structure.from_id
                                       and ps.npd_force is null
                                       and substit_npd_structure(ps.*) <> v_data.best_npd;
            end loop;
        end loop;
end$$;
--
alter table substit_structure disable trigger substit_trigger_on_structure_substit;
alter table structure disable trigger substit_trigger_structure;
--
select tmp__substit_update__migrate_structure_substit();
--
alter table structure enable trigger substit_trigger_structure;
alter table substit_structure enable trigger substit_trigger_on_structure_substit;
-- Les substitués doivent être historisés, mis à jour par la synchro même historisés, et non déhistorisés par la synchro.
update structure s set histo_destruction = current_timestamp, histo_destructeur_id = app_utilisateur_id(),
                       synchro_undelete_enabled = false, synchro_update_on_deleted_enabled = true
                   from substit_structure ss where s.id = ss.from_id;
-- Ces substituants ne doivent pas être mises à jour automatiquement par le moteur à partir des substituées.
update structure s set est_substituant_modifiable = false
                   from substit_structure ss where s.id = ss.to_id;
/*-- verif
with tmp as (
    select ss.id, ss.histo_creation, ss.histo_destruction, ss.npd npd_substit, ss.from_id,
           substit_npd_structure(ps.*) npd_calc, ps.npd_force, ss.to_id, s.histo_destruction, ps.libelle, s.libelle
    from substit_structure ss
    join structure ps on ss.from_id = ps.id
    join structure s on ss.to_id = s.id
    order by to_id, substit_npd_structure(ps.*)
)
select * from tmp where npd_force <> npd_calc;*/

--
-- Création des substitutions manquantes : update bidon des non substitués pour déclencher le trigger.
--
alter table structure enable trigger substit_trigger_structure;
alter table substit_structure enable trigger substit_trigger_on_structure_substit;
update structure set libelle = libelle -- 46 s
where id in (
    select v.id -- 578
    from v_structure_doublon v
    join structure ps on ps.id = v.id
    left join substit_structure ss on ps.id = ss.from_id
    where ss.to_id is null -- substitution manquante
    order by ps.id
)
returning id;
/* verif
Rejouer le inner select du update : ça doit être vide.
*/

--
-- Remplacements des FK.
--
select substit_replace_foreign_keys_values('structure'); -- 43 s
/* verif
Générer les requêtes. Chacune doit ramener 0 ligne.
--> select concat('select id from ',v.source_table,' r where exists (select from substit_structure ss where ss.from_id = r.',v.fk_column,' and ss.histo_destruction is null);') from v_substit_foreign_keys_structure v ;
select id from ecole_doct r where exists (select from substit_structure ss where ss.from_id = r.structure_id and ss.histo_destruction is null);
select id from etablissement r where exists (select from substit_structure ss where ss.from_id = r.structure_id and ss.histo_destruction is null);
select id from formation_formation r where exists (select from substit_structure ss where ss.from_id = r.type_structure_id and ss.histo_destruction is null);
select id from formation_session_structure_valide r where exists (select from substit_structure ss where ss.from_id = r.structure_id and ss.histo_destruction is null);
select id from formation_session r where exists (select from substit_structure ss where ss.from_id = r.type_structure_id and ss.histo_destruction is null);
select id from role r where exists (select from substit_structure ss where ss.from_id = r.structure_id and ss.histo_destruction is null);
select id from structure_document r where exists (select from substit_structure ss where ss.from_id = r.structure_id and ss.histo_destruction is null);
select id from unite_rech r where exists (select from substit_structure ss where ss.from_id = r.structure_id and ss.histo_destruction is null);
*/

--
-- Synchro
--
select('>> Lancer la synchro dans SyGAL.');



--================================================== ETABLISSEMENT ===================================================-

--
-- Création en bonne et dûe forme des substitutions 'etablissement' à partir des substitutions 'structure'.
-- ATTENTION : valable uniquement si aucune substitution automatique n'a été faite !
--
alter table substit_etablissement disable trigger substit_trigger_on_etablissement_substit;
alter table etablissement disable trigger substit_trigger_etablissement;
--
insert into substit_etablissement (histo_createur_id, from_id, to_id, npd)
    select app_utilisateur_id(), fe.id, te.id, substit_npd_etablissement(te.*)
    from substit_structure ss
             join etablissement fe on fe.structure_id = ss.from_id
             join etablissement te on te.structure_id = ss.to_id
    returning id, npd;
-- Les substitués doivent être historisés, mis à jour par la synchro même historisés, et non déhistorisés par la synchro.
update etablissement s set histo_destruction = current_timestamp, histo_destructeur_id = app_utilisateur_id(),
                       synchro_undelete_enabled = false, synchro_update_on_deleted_enabled = true
                       from substit_etablissement ss where s.id = ss.from_id;
-- Ces substituants ne doivent pas être mises à jour automatiquement par le moteur à partir des substituées.
update etablissement s set est_substituant_modifiable = false
                       from substit_etablissement ss where s.id = ss.to_id ;
-- Renseignement du NPD forcé dans les substitués pour que les substitutions manuelles soient conservées.
update etablissement ps
    set npd_force = es.npd
    from substit_etablissement es
    where ps.id = es.from_id and substit_npd_etablissement(ps.*) <> es.npd
    returning ps.id, substit_npd_etablissement(ps.*) npd_calcule, ps.npd_force;
/*-- verif
with tmp as (
    select ss.id, ss.histo_creation, ss.histo_destruction, ss.npd npd_substit, ss.from_id,
           substit_npd_etablissement(ps.*) npd_calc, ps.npd_force, ss.to_id, s.histo_destruction, ps.source_code, s.source_code
    from substit_etablissement ss
    join etablissement ps on ss.from_id = ps.id
    join etablissement s on ss.to_id = s.id
    order by to_id, substit_npd_etablissement(ps.*)
)
select * from tmp where npd_force <> npd_calc;
*/

--
-- Création des substitutions manquantes : update bidon des non substitués pour déclencher le trigger.
--
alter table substit_etablissement enable trigger substit_trigger_on_etablissement_substit;
alter table etablissement enable trigger substit_trigger_etablissement;
update etablissement set structure_id = structure_id -- 1m 3s
where id in (
    select v.id
    from v_etablissement_doublon v
             join etablissement ps on ps.id = v.id
             left join substit_etablissement ss on ps.id = ss.from_id
    where ss.to_id is null -- substitution manquante
    order by ps.id
);
/*-- vérif
Rejouer le inner select du update : ça doit être vide.
*/
/*-- verif que le substituant des substitutions manuelles n'a pas été mis à jour à partir des substitués :
select ss.id, ss.npd npd_substit, ss.from_id,
       ps.npd_force, ss.to_id, s.histo_destruction, ps.source_code, s.source_code,
       s.email_assistance, s.email_bibliotheque, s.est_etab_inscription, s.est_membre --> à verifier
from substit_etablissement ss
         join etablissement ps on ss.from_id = ps.id
         join etablissement s on ss.to_id = s.id
WHERE npd_force IS NOT NULL
order by to_id;
*/

--
-- Remplacements des FK.
--
select substit_replace_foreign_keys_values('etablissement');

--
-- Recherche de logs mentionnant un pb d'unicité empêchant le remplacement de FK :
--
select * from substit_log where type = 'etablissement' order by id desc;
select * from substit_log where type = 'etablissement' and operation = 'FK_REPLACE_PROBLEM' order by id desc;

--
-- Synchro
--
select('À lancer dans l interface.');

/*
with c as (
    select structure_id, count(*) from etablissement
                                       --where histo_destruction is null
    group by structure_id having count(*) > 1
)
select * from etablissement where structure_id in (select structure_id from c)
order by structure_id, histo_creation;

select * from structure where id in (301,321,323,324); -- pas de source 1
select * from substit_structure where from_id in (301,321,323,324);
select * from etablissement where id in (141,161,163,164) order by id; -- pas de source 1
select * from substit_etablissement where from_id in (141,161,163,164) order by from_id;
select * from substit_etablissement where to_id in (19458,19379,19448,19442) order by from_id;

select e.* from etablissement e
join substit_etablissement es on e.id = es.to_id and es.histo_destruction is null
left join substit_structure ss on ss.to_id = e.structure_id
where e.histo_destruction is null and ss.to_id is null
order by e.structure_id;
*/



--================================================== ECOLE_DOCT ===================================================-

--
-- Création en bonne et dûe forme des substitutions 'ecole_doct' à partir des substitutions 'structure'.
-- ATTENTION : valable uniquement si aucune substitution automatique n'a été faite !
--
alter table substit_ecole_doct disable trigger substit_trigger_on_ecole_doct_substit;
alter table ecole_doct disable trigger substit_trigger_ecole_doct;
--
insert into substit_ecole_doct (histo_createur_id, from_id, to_id, npd)
select app_utilisateur_id(), fe.id, te.id, substit_npd_ecole_doct(te.*)
from substit_structure ss
         join ecole_doct fe on fe.structure_id = ss.from_id
         join ecole_doct te on te.structure_id = ss.to_id
returning id, npd;
-- Les substitués doivent être historisés, mis à jour par la synchro même historisés, et non déhistorisés par la synchro.
update ecole_doct s set histo_destruction = current_timestamp, histo_destructeur_id = app_utilisateur_id(),
                        synchro_undelete_enabled = false, synchro_update_on_deleted_enabled = true
                    from substit_ecole_doct ss where s.id = ss.from_id;
-- Ces substituants ne doivent pas être mises à jour automatiquement par le moteur à partir des substituées.
update ecole_doct s set est_substituant_modifiable = false
                    from substit_ecole_doct ss where s.id = ss.to_id ;
-- Renseignement du NPD forcé dans les substitués pour que les substitutions manuelles soient conservées.
update ecole_doct ps
set npd_force = es.npd
from substit_ecole_doct es
where ps.id = es.from_id and substit_npd_ecole_doct(ps.*) <> es.npd
returning ps.id, substit_npd_ecole_doct(ps.*) npd_calcule, ps.npd_force;
/*-- verif
with tmp as (
    select ss.id, ss.histo_creation, ss.histo_destruction, ss.npd npd_substit, ss.from_id,
           substit_npd_ecole_doct(ps.*) npd_calc, ps.npd_force, ss.to_id, s.histo_destruction, ps.source_code, s.source_code
    from substit_ecole_doct ss
    join ecole_doct ps on ss.from_id = ps.id
    join ecole_doct s on ss.to_id = s.id
    order by to_id, substit_npd_ecole_doct(ps.*)
)
select * from tmp where npd_force <> npd_calc;*/

--
-- Création des substitutions manquantes : update bidon des non substituées pour déclencher le trigger.
--
alter table substit_ecole_doct enable trigger substit_trigger_on_ecole_doct_substit;
alter table ecole_doct enable trigger substit_trigger_ecole_doct;
update ecole_doct set structure_id = structure_id
where id in (
    select v.id
    from v_ecole_doct_doublon v
             join ecole_doct ps on ps.id = v.id
             left join substit_ecole_doct ss on ps.id = ss.from_id and ss.histo_destruction is null
    where ss.to_id is null -- substitution manquante
    order by ps.id
);
/*-- vérif
Rejouer le inner select du update : ça doit être vide.
*/
/*-- verif que le substituant des substitutions manuelles n'a pas été mis à jour à partir des substitués :
select ss.id, ss.npd npd_substit, ss.from_id,
       ps.npd_force, ss.to_id, s.histo_destruction, ps.source_code, s.source_code,
       s.offre_these, s.theme --> à verifier
from substit_ecole_doct ss
         join ecole_doct ps on ss.from_id = ps.id
         join ecole_doct s on ss.to_id = s.id
WHERE npd_force IS NOT NULL
order by to_id;*/


--
-- Remplacements des FK.
--
select substit_replace_foreign_keys_values('ecole_doct');

--
-- Recherche de logs mentionnant un pb d'unicité empêchant le remplacement de FK :
--
select * from substit_log where type = 'ecole_doct' order by id desc;
select * from substit_log where type = 'ecole_doct' and operation = 'FK_REPLACE_PROBLEM' order by id desc;

--
-- Synchro
--
select('À lancer dans l interface.');




--================================================== UNITE_RECH ===================================================-

--
-- Création en bonne et dûe forme des substitutions 'unite_rech' à partir des substitutions 'structure'.
-- ATTENTION : valable uniquement si aucune substitution automatique n'a été faite !
--
alter table substit_unite_rech disable trigger substit_trigger_on_unite_rech_substit;
alter table unite_rech disable trigger substit_trigger_unite_rech;
--
insert into substit_unite_rech (histo_createur_id, from_id, to_id, npd)
select app_utilisateur_id(), fe.id, te.id, substit_npd_unite_rech(te.*)
from substit_structure ss
         join unite_rech fe on fe.structure_id = ss.from_id
         join unite_rech te on te.structure_id = ss.to_id
returning id, npd;
-- Les substitués doivent être historisés, mis à jour par la synchro même historisés, et non déhistorisés par la synchro.
update unite_rech s set histo_destruction = current_timestamp, histo_destructeur_id = app_utilisateur_id(),
                        synchro_undelete_enabled = false, synchro_update_on_deleted_enabled = true
                    from substit_unite_rech ss where s.id = ss.from_id;
-- Ces substituants ne doivent pas être mises à jour automatiquement par le moteur à partir des substituées.
update unite_rech s set est_substituant_modifiable = false
                    from substit_unite_rech ss where s.id = ss.to_id ;
-- Renseignement du NPD forcé dans les substitués pour que les substitutions manuelles soient conservées.
update unite_rech ps
set npd_force = es.npd
from substit_unite_rech es
where ps.id = es.from_id and substit_npd_unite_rech(ps.*) <> es.npd
returning ps.id, substit_npd_unite_rech(ps.*) npd_calcule, ps.npd_force;
/*-- verif
with tmp as (
    select ss.id, ss.histo_creation, ss.histo_destruction, ss.npd npd_substit, ss.from_id,
           substit_npd_unite_rech(ps.*) npd_calc, ps.npd_force, ss.to_id, s.histo_destruction, ps.source_code, s.source_code
    from substit_unite_rech ss
    join unite_rech ps on ss.from_id = ps.id
    join unite_rech s on ss.to_id = s.id
    order by to_id, substit_npd_unite_rech(ps.*)
)
select * from tmp where npd_force <> npd_calc;*/

--
-- Création des substitutions manquantes : update bidon des non substituées pour déclencher le trigger.
--
alter table substit_unite_rech enable trigger substit_trigger_on_unite_rech_substit;
alter table unite_rech enable trigger substit_trigger_unite_rech;
update unite_rech set structure_id = structure_id
where id in (
    select v.id
    from v_unite_rech_doublon v
             join unite_rech ps on ps.id = v.id
             left join substit_unite_rech ss on ps.id = ss.from_id and ss.histo_destruction is null
    where ss.to_id is null -- substitution manquante
    order by ps.id
);
/*-- vérif
Rejouer le inner select du update : ça doit être vide.
*/
/*-- verif que le substituant des substitutions manuelles n'a pas été mis à jour à partir des substitués :
select ss.id, ss.npd npd_substit, ss.from_id,
       ps.npd_force, ss.to_id, s.histo_destruction, ps.source_code, s.source_code,
       s.offre_these, s.theme --> à verifier
from substit_unite_rech ss
         join unite_rech ps on ss.from_id = ps.id
         join unite_rech s on ss.to_id = s.id
WHERE npd_force IS NOT NULL
order by to_id;*/


--
-- Remplacements des FK.
--
select substit_replace_foreign_keys_values('unite_rech'); -- 12 s

--
-- Recherche de logs mentionnant un pb d'unicité empêchant le remplacement de FK :
--
select * from substit_log where type = 'unite_rech' order by id desc;
select * from substit_log where type = 'unite_rech' and operation = 'FK_REPLACE_PROBLEM' order by id desc;

--
-- Synchro
--
select('À lancer dans l interface.');



--================================================== INDIVIDU ===================================================-

--
-- Créations des substitutions possibles manquantes *SANS REMPLACEMENT DES FK*.
--
alter table substit_individu disable trigger substit_trigger_on_individu_substit;
select substit_create_all_substitutions_individu(); -- 2 h 6 m 4 s pour 6911 substitutions sur base de TEST
alter table substit_individu enable trigger substit_trigger_on_individu_substit;

--
-- Remplacements des FK.
--
select substit_replace_foreign_keys_values('individu'); -- 32 m 50 s pour 34743 remplacements sur base de TEST

-- --
-- -- Recherche de logs mentionnant un pb d'unicité empêchant le remplacement de FK :
-- --
-- select * from substit_log where type = 'individu' and operation = 'FK_REPLACE' order by id desc;
-- select * from substit_log where type = 'individu' and operation = 'FK_REPLACE_PROBLEM' order by id desc;
--
-- -- Liste des enregistrements dans 'individu_role' où le remplacement de la FK par l'id substituant
-- -- n'a pas pu être fait à cause de la contrainte d'unicité (individu_id, role_id).
-- -- (NB : cf. requête globale plus bas.)
-- select 'individu_role' t, r.id, 'individu_id' fk_name, individu_id fk_value, ss.to_id fk_value_required
-- from individu_role r
--     join substit_individu ss on from_id = r.individu_id ;



--================================================== DOCTORANT ===================================================-

--
-- Créations des substitutions possibles manquantes SANS REMPLACEMENT DES FK.
--
alter table substit_doctorant disable trigger substit_trigger_on_doctorant_substit;
select substit_create_all_substitutions_doctorant(); -- 1 m
alter table substit_doctorant enable trigger substit_trigger_on_doctorant_substit;

--
-- Remplacements des FK.
--
select substit_replace_foreign_keys_values('doctorant'); -- 1 m

--
-- Recherche de logs mentionnant un pb d'unicité empêchant le remplacement de FK :
--
select * from substit_log where type = 'doctorant' and operation = 'FK_REPLACE';
select * from substit_log where type = 'doctorant' and operation = 'FK_REPLACE_PROBLEM';






--=============================================================================================================-
--================================================== vérifs ===================================================-
--=============================================================================================================-

--
-- Vérif que toutes les substitutions possibles existent (d'après v_xxxx_doublon) :
--
select 'individu' t, v.id from v_individu_doublon v left join substit_individu ss on v.id = from_id and ss.histo_destruction is null where to_id is null union all
select 'doctorant' t, v.id from v_doctorant_doublon v left join substit_doctorant ss on v.id = from_id and ss.histo_destruction is null where to_id is null union all
select 'structure' t, v.id from v_structure_doublon v left join substit_structure ss on v.id = from_id and ss.histo_destruction is null where to_id is null union all
select 'etablissement' t, v.id from v_etablissement_doublon v left join substit_etablissement ss on v.id = from_id and ss.histo_destruction is null where to_id is null union all
select 'ecole_doct' t, v.id from v_ecole_doct_doublon v left join substit_ecole_doct ss on v.id = from_id and ss.histo_destruction is null where to_id is null union all
select 'unite_rech' t, v.id from v_unite_rech_doublon v left join substit_unite_rech ss on v.id = from_id and ss.histo_destruction is null where to_id is null
;

--
-- Cas particulier des substitutions d'1 seule structure par 1 autre
-- (héritage des anciennes substitutions faites à la main dans le but de changer le sigle par ex.) :
--
with substit_1_to_1 as (
    select to_id from substit_structure where histo_destruction is null group by to_id having count(*) = 1 -- 1 seul sustitué
)
select ps.code, ps.sigle, s.code, s.sigle
from substit_structure ss
    join substit_1_to_1 tmp on tmp.to_id = ss.to_id
join structure ps on ss.from_id = ps.id
join structure s on ss.to_id = s.id
;

--
-- Vérif des remplacements de FK :
--
-- 1/ Générer la requête.
select concat('select ''',v.source_table,''' t, r.id, ''',v.fk_column,''' fk_name, ',v.fk_column,' fk_value_current, ss.to_id fk_value_required from ',v.source_table,' r join substit_individu ss on from_id = r.',v.fk_column,' and ss.histo_destruction is null union all') from v_substit_foreign_keys_individu v union all
select concat('select ''',v.source_table,''' t, r.id, ''',v.fk_column,''' fk_name, ',v.fk_column,' fk_value_current, ss.to_id fk_value_required from ',v.source_table,' r join substit_doctorant ss on from_id = r.',v.fk_column,' and ss.histo_destruction is null union all') from v_substit_foreign_keys_doctorant v union all
select concat('select ''',v.source_table,''' t, r.id, ''',v.fk_column,''' fk_name, ',v.fk_column,' fk_value_current, ss.to_id fk_value_required from ',v.source_table,' r join substit_structure ss on from_id = r.',v.fk_column,' and ss.histo_destruction is null union all') from v_substit_foreign_keys_structure v union all
select concat('select ''',v.source_table,''' t, r.id, ''',v.fk_column,''' fk_name, ',v.fk_column,' fk_value_current, ss.to_id fk_value_required from ',v.source_table,' r join substit_etablissement ss on from_id = r.',v.fk_column,' and ss.histo_destruction is null union all') from v_substit_foreign_keys_etablissement v union all
select concat('select ''',v.source_table,''' t, r.id, ''',v.fk_column,''' fk_name, ',v.fk_column,' fk_value_current, ss.to_id fk_value_required from ',v.source_table,' r join substit_ecole_doct ss on from_id = r.',v.fk_column,' and ss.histo_destruction is null union all') from v_substit_foreign_keys_ecole_doct v union all
select concat('select ''',v.source_table,''' t, r.id, ''',v.fk_column,''' fk_name, ',v.fk_column,' fk_value_current, ss.to_id fk_value_required from ',v.source_table,' r join substit_unite_rech ss on from_id = r.',v.fk_column,' and ss.histo_destruction is null union all') from v_substit_foreign_keys_unite_rech v
;

-- 2/ Copier-coller la requête générée et la lancer.
-- Il doit y avoir 0 ligne.
-- Sauf pour 'individu_role' où la contrainte d'unicité (individu_id,role_id) a pu empêcher le remplacement.
select 'acteur' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from acteur r join substit_individu ss on from_id = r.individu_id and ss.histo_destruction is null union all
select 'doctorant' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from doctorant r join substit_individu ss on from_id = r.individu_id and ss.histo_destruction is null union all
select 'formation_formateur' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from formation_formateur r join substit_individu ss on from_id = r.individu_id and ss.histo_destruction is null union all
select 'formation_formation' t, r.id, 'responsable_id' fk_name, responsable_id fk_value_current, ss.to_id fk_value_required from formation_formation r join substit_individu ss on from_id = r.responsable_id and ss.histo_destruction is null union all
select 'formation_session' t, r.id, 'responsable_id' fk_name, responsable_id fk_value_current, ss.to_id fk_value_required from formation_session r join substit_individu ss on from_id = r.responsable_id and ss.histo_destruction is null union all
select 'individu_role' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from individu_role r join substit_individu ss on from_id = r.individu_id and ss.histo_destruction is null union all
select 'mail_confirmation' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from mail_confirmation r join substit_individu ss on from_id = r.individu_id and ss.histo_destruction is null union all
select 'rapport_activite_validation' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from rapport_activite_validation r join substit_individu ss on from_id = r.individu_id and ss.histo_destruction is null union all
select 'rapport_validation' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from rapport_validation r join substit_individu ss on from_id = r.individu_id and ss.histo_destruction is null union all
select 'individu_compl' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from individu_compl r join substit_individu ss on from_id = r.individu_id and ss.histo_destruction is null union all
select 'utilisateur' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from utilisateur r join substit_individu ss on from_id = r.individu_id and ss.histo_destruction is null union all
select 'validation' t, r.id, 'individu_id' fk_name, individu_id fk_value_current, ss.to_id fk_value_required from validation r join substit_individu ss on from_id = r.individu_id and ss.histo_destruction is null union all
select 'z_doctorant_compl' t, r.id, 'doctorant_id' fk_name, doctorant_id fk_value_current, ss.to_id fk_value_required from z_doctorant_compl r join substit_doctorant ss on from_id = r.doctorant_id and ss.histo_destruction is null union all
select 'doctorant_mission_enseignement' t, r.id, 'doctorant_id' fk_name, doctorant_id fk_value_current, ss.to_id fk_value_required from doctorant_mission_enseignement r join substit_doctorant ss on from_id = r.doctorant_id and ss.histo_destruction is null union all
select 'formation_inscription' t, r.id, 'doctorant_id' fk_name, doctorant_id fk_value_current, ss.to_id fk_value_required from formation_inscription r join substit_doctorant ss on from_id = r.doctorant_id and ss.histo_destruction is null union all
select 'these' t, r.id, 'doctorant_id' fk_name, doctorant_id fk_value_current, ss.to_id fk_value_required from these r join substit_doctorant ss on from_id = r.doctorant_id and ss.histo_destruction is null union all
select 'ecole_doct' t, r.id, 'structure_id' fk_name, structure_id fk_value_current, ss.to_id fk_value_required from ecole_doct r join substit_structure ss on from_id = r.structure_id and ss.histo_destruction is null union all
select 'etablissement' t, r.id, 'structure_id' fk_name, structure_id fk_value_current, ss.to_id fk_value_required from etablissement r join substit_structure ss on from_id = r.structure_id and ss.histo_destruction is null union all
select 'formation_formation' t, r.id, 'type_structure_id' fk_name, type_structure_id fk_value_current, ss.to_id fk_value_required from formation_formation r join substit_structure ss on from_id = r.type_structure_id and ss.histo_destruction is null union all
select 'formation_session_structure_valide' t, r.id, 'structure_id' fk_name, structure_id fk_value_current, ss.to_id fk_value_required from formation_session_structure_valide r join substit_structure ss on from_id = r.structure_id and ss.histo_destruction is null union all
select 'formation_session' t, r.id, 'type_structure_id' fk_name, type_structure_id fk_value_current, ss.to_id fk_value_required from formation_session r join substit_structure ss on from_id = r.type_structure_id and ss.histo_destruction is null union all
select 'role' t, r.id, 'structure_id' fk_name, structure_id fk_value_current, ss.to_id fk_value_required from role r join substit_structure ss on from_id = r.structure_id and ss.histo_destruction is null union all
select 'structure_document' t, r.id, 'structure_id' fk_name, structure_id fk_value_current, ss.to_id fk_value_required from structure_document r join substit_structure ss on from_id = r.structure_id and ss.histo_destruction is null union all
select 'unite_rech' t, r.id, 'structure_id' fk_name, structure_id fk_value_current, ss.to_id fk_value_required from unite_rech r join substit_structure ss on from_id = r.structure_id and ss.histo_destruction is null union all
select 'acteur' t, r.id, 'acteur_etablissement_id' fk_name, acteur_etablissement_id fk_value_current, ss.to_id fk_value_required from acteur r join substit_etablissement ss on from_id = r.acteur_etablissement_id and ss.histo_destruction is null union all
select 'doctorant' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from doctorant r join substit_etablissement ss on from_id = r.etablissement_id and ss.histo_destruction is null union all
select 'formation_formation' t, r.id, 'site_id' fk_name, site_id fk_value_current, ss.to_id fk_value_required from formation_formation r join substit_etablissement ss on from_id = r.site_id and ss.histo_destruction is null union all
select 'formation_session' t, r.id, 'site_id' fk_name, site_id fk_value_current, ss.to_id fk_value_required from formation_session r join substit_etablissement ss on from_id = r.site_id and ss.histo_destruction is null union all
select 'individu_compl' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from individu_compl r join substit_etablissement ss on from_id = r.etablissement_id and ss.histo_destruction is null union all
select 'etablissement_rattach' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from etablissement_rattach r join substit_etablissement ss on from_id = r.etablissement_id and ss.histo_destruction is null union all
select 'structure_document' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from structure_document r join substit_etablissement ss on from_id = r.etablissement_id and ss.histo_destruction is null union all
select 'source' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from source r join substit_etablissement ss on from_id = r.etablissement_id and ss.histo_destruction is null union all
select 'these' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from these r join substit_etablissement ss on from_id = r.etablissement_id and ss.histo_destruction is null union all
select 'variable' t, r.id, 'etablissement_id' fk_name, etablissement_id fk_value_current, ss.to_id fk_value_required from variable r join substit_etablissement ss on from_id = r.etablissement_id and ss.histo_destruction is null union all
select 'these' t, r.id, 'ecole_doct_id' fk_name, ecole_doct_id fk_value_current, ss.to_id fk_value_required from these r join substit_ecole_doct ss on from_id = r.ecole_doct_id and ss.histo_destruction is null union all
select 'acteur' t, r.id, 'acteur_uniterech_id' fk_name, acteur_uniterech_id fk_value_current, ss.to_id fk_value_required from acteur r join substit_unite_rech ss on from_id = r.acteur_uniterech_id and ss.histo_destruction is null union all
select 'individu_compl' t, r.id, 'unite_id' fk_name, unite_id fk_value_current, ss.to_id fk_value_required from individu_compl r join substit_unite_rech ss on from_id = r.unite_id and ss.histo_destruction is null union all
select 'etablissement_rattach' t, r.id, 'unite_id' fk_name, unite_id fk_value_current, ss.to_id fk_value_required from etablissement_rattach r join substit_unite_rech ss on from_id = r.unite_id and ss.histo_destruction is null union all
select 'these' t, r.id, 'unite_rech_id' fk_name, unite_rech_id fk_value_current, ss.to_id fk_value_required from these r join substit_unite_rech ss on from_id = r.unite_rech_id and ss.histo_destruction is null
;



--=============== cas particuliers =================--

with doublons as (
    select * from v_individu_doublon
    where upper(nom_patronymique) in (
        'FRANCK',                       -- Xavier Franck :https://sygal.normandie-univ.fr/utilisateur/voir/33019 : signalé par Emilie
        'FATYEYEVA',                    -- Kateryana Fatyeyeva : https://sygal.normandie-univ.fr/utilisateur/voir/58883 : idem
        'BLAISOT',                      -- Jean-Bernard Blaisot : https://sygal.normandie-univ.fr/utilisateur/voir/67663 : idem
        'CHETELAT',                     -- Gaelle Chetelat : https://sygal.normandie-univ.fr/utilisateur/voir/40481 : signalé par Véro
        'JEAN-MARIE', 'JEAN MARIE',     -- Laurence Jean-Marie : https://sygal.normandie-univ.fr/utilisateur/voir/73423 : idem
        'VILLEDIEU'                     -- Marie Villedieu : https://sygal.normandie-univ.fr/utilisateur/voir/134803 : idem
        --'HOCHAN', 'VIEILLE', 'BERNAUDIN', 'BRANDLE DE MOTTA', 'DEMOULIN', 'DURET'
        )
    order by nom_patronymique
)
select i.*, ir.*
from individu i
join doublons d on d.id = i.id
join individu_role ir on i.id = ir.individu_id
--where i.id in ('863784','39729')
;


--
-- individu_role
select i.nom_patronymique, i.prenom1, ir.*
from individu_role ir
join substit_individu sub on sub.to_id = ir.individu_id
join individu i on ir.individu_id = i.id
where upper(i.nom_patronymique) in (
--     'FRANCK',                       -- Xavier Franck :https://sygal.normandie-univ.fr/utilisateur/voir/33019 : signalé par Emilie
--     'FATYEYEVA',                    -- Kateryana Fatyeyeva : https://sygal.normandie-univ.fr/utilisateur/voir/58883 : idem
    'BLAISOT',                      -- Jean-Bernard Blaisot : https://sygal.normandie-univ.fr/utilisateur/voir/67663 : idem
--     'CHETELAT',                     -- Gaelle Chetelat : https://sygal.normandie-univ.fr/utilisateur/voir/40481 : signalé par Véro
    'JEAN-MARIE', 'JEAN MARIE'     -- Laurence Jean-Marie : https://sygal.normandie-univ.fr/utilisateur/voir/73423 : idem
--     'VILLEDIEU'                     -- Marie Villedieu : https://sygal.normandie-univ.fr/utilisateur/voir/134803 : idem
    --'HOCHAN', 'VIEILLE', 'BERNAUDIN', 'BRANDLE DE MOTTA', 'DEMOULIN', 'DURET'
);

--
-- acteur
select i.nom_patronymique, i.prenom1, ir.*
from acteur ir
         join substit_individu sub on sub.to_id = ir.individu_id
         join individu i on ir.individu_id = i.id
where upper(i.nom_patronymique) in (
    'BLAISOT',                      -- Jean-Bernard Blaisot : https://sygal.normandie-univ.fr/utilisateur/voir/67663 : signalé par Emilie
--     'FRANCK',                       -- Xavier Franck :https://sygal.normandie-univ.fr/utilisateur/voir/33019 : idem
--     'FATYEYEVA',                    -- Kateryana Fatyeyeva : https://sygal.normandie-univ.fr/utilisateur/voir/58883 : idem
--     'CHETELAT',                     -- Gaelle Chetelat : https://sygal.normandie-univ.fr/utilisateur/voir/40481 : signalé par Véro
    'JEAN-MARIE', 'JEAN MARIE'     -- Laurence Jean-Marie : https://sygal.normandie-univ.fr/utilisateur/voir/73423 : idem
--     'VILLEDIEU'                     -- Marie Villedieu : https://sygal.normandie-univ.fr/utilisateur/voir/134803 : idem
    --'HOCHAN', 'VIEILLE', 'BERNAUDIN', 'BRANDLE DE MOTTA', 'DEMOULIN', 'DURET'
    );


select * from utilisateur where individu_id in (1184130, 1185160);
select * from doctorant where individu_id in (1184130, 1185160);
select * from these where doctorant_id = 25812;



select * from doctorant where source_code in ('UCN::20008144','UCN::21313055','UCN::20406116','UCN::20407482','UCN::20408648' );
select * from substit_doctorant where from_id in (select id from doctorant where source_code in ('UCN::20008144','UCN::21313055','UCN::20406116','UCN::20407482','UCN::20408648'));

select * from doctorant where ine = '2493251751B';


with logs as (
    select substitue_id, substituant_id from substit_log where type = 'individu' and operation = 'FK_REPLACE_PROBLEM'
),
     tmp as (
         select 'x ' f, ir.*, sub.to_id, i.nom_usuel
         from individu_role ir
                  join individu i on ir.individu_id = i.id
                  join substit_individu sub on sub.from_id = ir.individu_id --and sub.histo_destruction is null
         where individu_id in (select substitue_id from logs)
         union all
         select 'done', ir.*, null, i.nom_usuel
         from individu_role ir
                  join individu i on ir.individu_id = i.id
         where individu_id in (select substituant_id from logs)
     )
select * from tmp order by role_id, f;