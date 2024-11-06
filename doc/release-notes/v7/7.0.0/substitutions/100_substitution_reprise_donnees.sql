--
-- Substitutions : reprise de données
--

--================================================== STRUCTURE ===================================================-

--
-- Abandon des anciennes substitutions d'une seule structure (non supportées par le nouveau moteur de substitutions) :
--   - récupération d'infos sur la structure substituée : chemin_logo, id_ref, id_hal ;
--   - suppression de la substitution ;
--   - NB : on perd les eventuelles corrections de colonnes importées (ex: sigle) => corrections à faire dans le SI amont.
--
create or replace function tmp__substit_update__logos_substit_structure() returns void
    language plpgsql
as
$$declare
    v_data record;
begin
    -- parcours des substitutions d'1 seule structure
    for v_data in with one_to_one as (select to_structure_id, count(*) from sav__structure_substit ss group by to_structure_id having count(*) = 1)
                  select ss.id, ss.from_structure_id, ss.to_structure_id
                  from sav__structure_substit ss
                           join structure s on s.id = ss.to_structure_id
                           join one_to_one on ss.to_structure_id = one_to_one.to_structure_id
        loop
            update structure sfrom set chemin_logo = sto.chemin_logo, id_ref = sto.id_ref, id_hal = sto.id_hal
                                   from structure sto where sfrom.id = v_data.from_structure_id
                                                        and sto.id = v_data.to_structure_id;
            delete from substit_structure where id = v_data.id;
        end loop;
end$$;

--
-- Migration des substitutions de structures pré-existantes.
-- ATTENTION : valable uniquement si aucune substitution automatique n'a été faite !
--
-- Principe :
--   - Alimentation des NPD dans les substitutions existantes (table 'substit_structure') ;
--   - Renseignement du NPD forcés dans les 'structure' où c'est nécessaire.
--
create or replace function tmp__substit_update__migrate_substit_structure() returns void
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
alter table substit_structure disable trigger substit_trigger_on_substit_structure;
alter table structure disable trigger substit_trigger_structure;
--
select tmp__substit_update__logos_substit_structure();
select tmp__substit_update__migrate_substit_structure();
--
alter table structure enable trigger substit_trigger_structure;
alter table substit_structure enable trigger substit_trigger_on_substit_structure;
-- Les substitués sont historisés. Config synchro : ils doivent pouvoir être mis à jour même historisés, et non déhistorisables.
update structure s set histo_destruction = current_timestamp, histo_destructeur_id = app_utilisateur_id(),
                       synchro_undelete_enabled = false, synchro_update_on_deleted_enabled = true
                   from substit_structure ss where s.id = ss.from_id;
-- Les substituants ne doivent pas être mis à jour automatiquement par le moteur à partir des substitués.
update structure s set est_substituant_modifiable = false
                   from substit_structure ss where s.id = ss.to_id;
/*-- verif
with tmp as (
    select ss.id, ss.histo_creation, ss.npd npd_substit, ss.from_id,
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
alter table substit_structure enable trigger substit_trigger_on_substit_structure;
update structure set libelle = libelle -- 2-3 min
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
select substit_replace_foreign_keys_values('structure'); -- 2 min
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
alter table substit_etablissement disable trigger substit_trigger_on_substit_etablissement;
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
alter table substit_etablissement enable trigger substit_trigger_on_substit_etablissement;
alter table etablissement enable trigger substit_trigger_etablissement;
update etablissement set structure_id = structure_id -- 1m 11s
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
select substit_replace_foreign_keys_values('etablissement'); -- 40 s

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
alter table substit_ecole_doct disable trigger substit_trigger_on_substit_ecole_doct;
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
alter table substit_ecole_doct enable trigger substit_trigger_on_substit_ecole_doct;
alter table ecole_doct enable trigger substit_trigger_ecole_doct;
update ecole_doct set structure_id = structure_id
where id in (
    select v.id
    from v_ecole_doct_doublon v
             join ecole_doct ps on ps.id = v.id
             left join substit_ecole_doct ss on ps.id = ss.from_id
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
alter table substit_unite_rech disable trigger substit_trigger_on_substit_unite_rech;
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
alter table substit_unite_rech enable trigger substit_trigger_on_substit_unite_rech;
alter table unite_rech enable trigger substit_trigger_unite_rech;
update unite_rech set structure_id = structure_id
where id in (
    select v.id
    from v_unite_rech_doublon v
             join unite_rech ps on ps.id = v.id
             left join substit_unite_rech ss on ps.id = ss.from_id
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
alter table substit_individu disable trigger substit_trigger_on_substit_individu;
select substit_create_all_substitutions_individu(); -- 1 h 35 m >> Nombre de substitutions créées : 4018
alter table substit_individu enable trigger substit_trigger_on_substit_individu;

--
-- Remplacements des FK.
--
select substit_replace_foreign_keys_values('individu'); -- 22 m 47 s pour 36915 remplacements

-- --
-- -- Recherche de logs mentionnant un pb d'unicité empêchant le remplacement de FK :
-- --
select * from substit_log where type = 'individu' and operation = 'FK_REPLACE' order by id desc;
select * from substit_log where type = 'individu' and operation = 'FK_REPLACE_PROBLEM' order by id desc;
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
alter table substit_doctorant disable trigger substit_trigger_on_substit_doctorant;
select substit_create_all_substitutions_doctorant();
alter table substit_doctorant enable trigger substit_trigger_on_substit_doctorant;

--
-- Remplacements des FK.
--
select substit_replace_foreign_keys_values('doctorant'); -- 1 m

--
-- Recherche de logs mentionnant un pb d'unicité empêchant le remplacement de FK :
--
select * from substit_log where type = 'doctorant' and operation = 'FK_REPLACE';
select * from substit_log where type = 'doctorant' and operation = 'FK_REPLACE_PROBLEM';

