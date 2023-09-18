--
-- Substitutions : reprise de données
--

--================================================== STRUCTURE ===================================================-
/*
select distinct(histo_creation) from structure_substit order by histo_creation desc;
delete from structure_substit where histo_creation in ('2023-07-26 12:20:12.473083', '2023-07-25 14:18:43.178524'   );
alter table pre_structure disable trigger substit_trigger_pre_structure;
update structure_substit set npd = null;
update pre_structure set npd_force = null;
alter table pre_structure enable trigger substit_trigger_pre_structure;
select * from pre_structure where source_code='UCN::FRE3101';
select * from structure where id=12928;
select * from structure_substit where from_id=38;
*/

--
-- Migration des substitutions de structures pré-existantes :
--   - Alimentation des NPD dans les substitutions existantes (table 'structure_substit') ;
--   - Renseignement du NPD forcés dans les 'pre_structure' où c'est nécessaire.
--
create or replace function tmp__substit_update__migrate_structure_substit() returns void
    language plpgsql
as
$$declare
    v_data record;
    v_pre_structure pre_structure;
    v_structure_substit structure_substit;
begin
    -- parcours des structures substituantes (pour chacune, on détermine le NPD majoritaire à partir des substituées).
    for v_data in select ss.to_id, mode() within group (order by substit_npd_structure(ps.*)) as best_npd -- NPD majoritaire
                  from structure_substit ss join pre_structure ps on ss.from_id = ps.id
                  group by ss.to_id
        loop
            -- S'il est null, update du NPD de la substitution avec le NPD proposé.
            update structure_substit set npd = v_data.best_npd,
                                         histo_modification = current_timestamp,
                                         histo_modificateur_id = app_utilisateur_id()
                                     where to_id = v_data.to_id
                                       and npd is null;
            -- Pour pérenniser la substitution, on met le NPD proposé comme NPD forcé dans chaque substitué, ssi :
            --   - le substitué n'a pas déjà un NPD forcé ;
            --   - le NPD proposé diffère du NPD par défaut.
            for v_structure_substit in select * from structure_substit where to_id = v_data.to_id loop
                update pre_structure ps set npd_force = v_data.best_npd
                                     where ps.id = v_structure_substit.from_id
                                       and ps.npd_force is null
                                       and substit_npd_structure(ps.*) <> v_data.best_npd;
            end loop;
        end loop;
end$$;
alter table pre_structure disable trigger substit_trigger_pre_structure;
select tmp__substit_update__migrate_structure_substit();
alter table pre_structure enable trigger substit_trigger_pre_structure;
/*** verif ***
with tmp as (
    select ss.id, ss.histo_creation, ss.histo_destruction, ss.npd npd_substit, ss.from_id,
           substit_npd_structure(ps.*) npd_calc, ps.npd_force, ss.to_id, s.histo_destruction, ps.libelle, s.libelle
    from structure_substit ss
    join pre_structure ps on ss.from_id = ps.id
    join structure s on ss.to_id = s.id
    order by to_id, substit_npd_structure(ps.*)
)
select * from tmp where npd_force <> npd_calc;
*/

/*
select * from  pre_structure where id in (4183);
select * from  pre_structure where id in (13107);
update pre_structure set libelle = libelle where id = 13107;
update pre_structure set libelle = 'Université de Caen Normandie' where id = 1139;
update pre_structure set libelle = 'Université de Caen Normandie' where id = 2931;
update pre_structure set libelle = 'Université de Caen Normandie' where id = 3026;

update pre_structure set histo_destruction = current_timestamp, histo_destructeur_id=1 where id = 13107;

select * from tmp_structure where source_code in ( 'UCN::UMR6552',    'UCN::EA4650','UCN::EA2129','UCN::EA7478','UCN::EA967');
select * from pre_structure where source_code in ( 'UCN::UMR6552',    'UCN::EA4650','UCN::EA2129','UCN::EA7478','UCN::EA967');
select * from src_structure where source_code in ( 'UCN::UMR6552',    'UCN::EA4650','UCN::EA2129','UCN::EA7478','UCN::EA967');
select * from structure where source_code in ( 'UCN::UMR6552',    'UCN::EA4650','UCN::EA2129','UCN::EA7478','UCN::EA967');
select * from structure_substit ss join pre_structure ps on ss.from_id = ps.id where source_code in ( 'UCN::UMR6552',    'UCN::EA4650','UCN::EA2129','UCN::EA7478','UCN::EA967');
select * from structure where id in (13581,    13595,13607,13594,13627    );
*/

--
-- Création des substitutions manquantes : update bidon des pre_structure substituables mais non substituées
-- pour déclencher le trigger.
--
alter table pre_structure enable trigger substit_trigger_pre_structure;
update pre_structure set libelle = libelle
where id in (
    select v.id -- 578
    from v_structure_doublon v
    join pre_structure ps on ps.id = v.id
    left join structure_substit ss on ps.id = ss.from_id and ss.histo_destruction is null
    where ss.to_id is null -- substitution manquante
    order by ps.id
    --
);
/* verif
select distinct ss.from_id, ps.libelle -- 262
from structure_substit ss
         join pre_structure ps on from_id = ps.id
         join structure s on to_id = s.id
where ss.histo_destruction is null
order by ps.libelle;
*/

--
-- Remplacements des FK.
--
select substit_replace_foreign_keys_values('structure');


--
-- Synchro des STRUCTURE, ETAB, ED, UR.
--
----> À lancer dans l'interface.




--================================================== INDIVIDU ===================================================-

select * from v_individu_doublon
where nom_patronymique in ('HOCHAN', 'VIEILLE', 'BERNAUDIN', 'BRANDLE DE MOTTA', 'DEMOULIN', 'DURET')
order by nom_patronymique;

-- créations des substitutions possibles manquantes.
select substit_create_all_substitutions_individu(); -- totalité : 32 min (avec ou sans les raise)

--
-- Remplacements des FK manquants :::::::::::: PLUS UTILE A PRIORI (fait par trigger sur xxx_substit)...
--
select substit_replace_foreign_keys_values('individu', ss.from_id, ss.to_id)
from individu_substit ss
where histo_destruction is null;

-- Liste des enregistrements dans 'individu_role' où le remplacement de la FK par l'id substituant
-- n'a pas pu être fait à cause de la contrinate d'unicité (individu_id, role_id) :
--select * from individu_role ir join individu_substit sub on sub.from_id = ir.individu_id ;


--================================================== DOCTORANT ===================================================-

-- créations des substitutions possibles manquantes.
select substit_create_all_substitutions_doctorant();

--
-- Remplacements des FK manquants :::::::::::: PLUS UTILE A PRIORI (fait par trigger sur xxx_substit)...
--
select substit_replace_foreign_keys_values('doctorant', ss.from_id, ss.to_id)
from doctorant_substit ss
where histo_destruction is null;



