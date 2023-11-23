--
-- Substitutions
--

--=============================== STRUCTURE ================================-

--
-- Vue listant les clés étrangères (FK) pointant vers 'structure'
-- dont la valeur doit être remplacée par l'id substituant éventuel.
--
-- drop view v_substit_foreign_keys_structure;
create or replace view v_substit_foreign_keys_structure as
select * from v_substit_foreign_keys
where target_table = 'structure'
  and source_table <> 'structure'
  and source_table <> 'structure_substit'
  and not (
    source_table = 'etablissement' and fk_column = 'structure_id' or
    source_table = 'ecole_doct' and fk_column = 'structure_id' or
    source_table = 'unite_rech' and fk_column = 'structure_id'
  )
;


--
-- Mise à jour table STRUCTURE
--
alter table structure add est_substituant bool default false not null;
comment on column structure.est_substituant is 'Indique s''il s''agit d''un enregistrement substituant d''autres enregistrements';
alter table structure add est_substituant_modifiable bool default true not null;
comment on column structure.est_substituant_modifiable is 'Indique si ce substituant (le cas échéant) peut être mis à jour à partir des attributs des substitués';
alter table structure alter column code set not null;
alter table structure alter column code drop default;
create index structure_code_index on structure (code);

-- sauvegardes tables
create table structure_sav as select * from structure;
create table structure_substit_sav as select * from structure_substit;

-- nouvelle table PRE_STRUCTURE
create table pre_structure (like structure including all);
-- alter table pre_structure disable trigger substit_trigger_pre_structure;
insert into pre_structure select * from structure;
-- alter table pre_structure enable trigger substit_trigger_pre_structure;
alter table pre_structure add column npd_force varchar(256);
alter table pre_structure drop column chemin_logo;
alter table pre_structure drop column est_ferme;
alter table pre_structure drop column adresse;
alter table pre_structure drop column telephone;
alter table pre_structure drop column fax;
alter table pre_structure drop column email;
alter table pre_structure drop column site_web;
alter table pre_structure drop column id_ref;
alter table pre_structure drop column id_hal;
alter table pre_structure add constraint pre_structure_source_fk foreign key (source_id) references source on delete cascade;
alter table pre_structure add constraint pre_structure_hc_fk foreign key (histo_createur_id) references utilisateur on delete cascade;
alter table pre_structure add constraint pre_structure_hm_fk foreign key (histo_modificateur_id) references utilisateur on delete cascade;
alter table pre_structure add constraint pre_structure_hd_fk foreign key (histo_destructeur_id) references utilisateur on delete cascade;
alter table pre_structure add constraint pre_structure_type_fk foreign key (type_structure_id) references type_structure on delete cascade;
create sequence if not exists pre_structure_id_seq owned by pre_structure.id;
alter table pre_structure alter column id set default nextval('structure_id_seq'); -- NB : utilisation de la sequence de la table finale
select setval('pre_structure_id_seq', (select max(id) from structure));

alter table structure_substit rename column from_structure_id to from_id;
alter table structure_substit rename column to_structure_id to to_id;
alter table structure_substit alter column histo_modification drop not null;
alter table structure_substit alter column histo_modification drop default;
alter table structure_substit add npd varchar(256);
alter table structure_substit drop constraint str_substit_str_from_fk;
alter table structure_substit add constraint str_substit_str_from_fk foreign key (from_id) references pre_structure (id);
alter table structure_substit drop constraint str_substit_str_to_fk;
alter table structure_substit add constraint str_substit_str_to_fk foreign key (to_id) references structure;
create sequence if not exists structure_substit_id_seq owned by structure_substit.id;
alter table structure_substit alter column id set default nextval('structure_substit_id_seq');
drop index str_substit_unique;
create unique index structure_substit_unique_idx on structure_substit(from_id) where histo_destruction is null;
create unique index structure_substit_unique_hist_idx on structure_substit(from_id, histo_destruction) where histo_destruction is not null;

drop view if exists v_diff_pre_structure;
drop view if exists src_pre_structure;
create or replace view src_pre_structure as
    select null::bigint as id,
           tmp.source_code,
           ltrim(substr(tmp.source_code::text, strpos(tmp.source_code::text, '::'::text)), ':'::text) AS code,
           src.id AS source_id,
           ts.id AS type_structure_id,
           tmp.sigle,
           tmp.libelle
    FROM tmp_structure tmp
             JOIN type_structure ts ON ts.code = tmp.type_structure_id
             JOIN source src ON src.id = tmp.source_id;

drop view if exists v_diff_structure;
drop view if exists src_structure;
create or replace view src_structure as
    select id,
           source_id,
           source_code,
           type_structure_id,
           code,
           libelle,
           sigle
    from pre_structure pre
    where pre.histo_destruction is null and not exists (
        select id from structure_substit where histo_destruction is null and from_id = pre.id
    );
/*
create or replace view src_structure as
select pre.id,
       pre.source_id,
       pre.source_code,
       pre.type_structure_id,
       pre.code,
       pre.libelle,
       pre.sigle,
       coalesce(sub.histo_creation, pre.histo_destruction) histo_destruction,
       coalesce(sub.histo_createur_id, pre.histo_destructeur_id) histo_destructeur_id
from pre_structure pre
left join structure_substit sub on from_id = pre.id and sub.histo_destruction is null;
*/

--
-- Trigger sur la table PRE_STRUCTURE se déclenchant en cas d'insertion ou de mise à jour de l'un des attributs suivants :
--   - attributs participant au NPD (code)
--   - NPD forcé.
drop trigger if exists substit_trigger_pre_structure on pre_structure;
create trigger substit_trigger_pre_structure
    after insert
        or delete
        or update of
            type_structure_id, code, -- pour entrer ou sortir d'une substitution éventuelle (NPD)
            --
            sigle, libelle, -- pour mettre à jour le substituant éventuel
            --
            npd_force, -- pour réagir à une demande de substitution forcée
            histo_destruction, -- pour réagir à l'historisation/restauration d'un enregsitrement
            source_id -- pour réagir au changement de source (source application => source compatible, et vice-versa)
    on pre_structure
    for each row
execute procedure substit_trigger_on_pre_fct('structure');

--
-- Trigger sur la table STRUCTURE se déclenchant en cas d'insertion d'un enregistrement potentiellement substituant,
-- en vue de remplacer partout où c'est nécessaire les valeurs des clés étrangères par l'id du substituant.
--
drop trigger if exists substit_trigger_on_structure_substit on structure_substit;
create trigger substit_trigger_on_structure_substit
    after insert
    or update of histo_destruction
    or delete
    on structure_substit
    for each row
execute procedure substit_trigger_on_substit_fct('structure');

alter table pre_structure enable trigger substit_trigger_pre_structure;
alter table structure_substit enable trigger substit_trigger_on_structure_substit;


create or replace function substit_npd_structure(structure pre_structure) returns varchar
    language plpgsql
as
$$declare
    v_code_type_struct varchar(64);
begin
    --
    -- Fonction de calcul du "NPD" d'une structure.
    --
    -- Si 2 enregistrements ont le même NPD alors ils sont considérés comme des doublons.
    --
    -- Important : Le NPD d'une structure "inclut" le 'code' du type de structure, pour se prémunir du cas
    -- (certes improbable) où 2 structures de types différents auraient le même 'code'.
    --
    -- Attention !
    -- Modifier le calcul du NPD n'est pas une mince affaire car cela remet en question les substitutions existantes
    -- définies dans la table 'xxxx_substit'.
    -- > Dans les 2 cas qui suivent, il faudra absolument désactiver au préalable les triggers suivants :
    --   - substit_trigger_pre_xxxx
    --   - substit_trigger_on_xxxx_substit
    -- > Dans le cas où cela ne change rien aux substitutions existantes, il faudra tout de même :
    --   - mettre à jour les valeurs dans la colonne 'npd' de la table 'xxxx_substit' en faisant appel
    --     à la présente fonction ;
    --   - mettre à jour manuellement les valeurs dans la colonne 'npd_force" de la table 'pre_xxxx'.
    -- > Dans le cas où cela invalide des substitutions existantes, il faudra :
    --   - historiser les substitutions concernées dans la table 'xxxx_substit' ;
    --   - mettre à jour manuellement les valeurs dans la colonne 'npd_force" de la table 'pre_xxxx'.
    --

    select code into v_code_type_struct from type_structure where id = structure.type_structure_id;

    return v_code_type_struct || ',' || structure.code;
end;
$$;


--
-- Vue retournant les structures en doublon au regard de leur NPD.
--
-- Rappel : Les structures en doublons ne sont pas recherchées dans la source correspondant à l'application.
--
--drop view v_structure_doublon;
create or replace view v_structure_doublon as
with structures_npd as (
    select coalesce(npd_force, substit_npd_structure(pre.*)) as npd, id, code
    from pre_structure pre
    where histo_destruction is null and source_id <> app_source_id()
), npds(npd) as (
    select npd, count(*)
    from structures_npd
    group by npd
    having count(*) > 1
)
select i.id, i.code, npds.npd
from npds, structures_npd i
where i.npd = npds.npd
--order by i.code
;

--drop function substit_fetch_data_for_substituant_structure;
create or replace function substit_fetch_data_for_substituant_structure(p_npd varchar)
    returns table
            (
                type_structure_id bigint,
                sigle             varchar,
                libelle           varchar,
                code              varchar
            )
    language plpgsql
as
$$begin
    --
    -- Détermination des meilleures valeurs d'attributs des structures en doublon en vue de les affecter à
    -- la structure substituante.
    --
    -- Pour chaque attribut, la stratégie de choix de la "meilleure" valeur est la fonction 'mode()'
    -- (cf. https://www.postgresql.org/docs/current/functions-aggregate).
    --
    -- Rappel : les structures en doublon sont les structures ayant le même NPD et n'appartenant pas à la source
    -- correspondnant à l'application.
    --

    raise notice 'Calcul des meilleures valeurs d''attributs parmi les doublons dont le NPD est %...', p_npd;

    return query
        select
            mode() within group (order by i.type_structure_id) as type_structure_id,
            mode() within group (order by trim(i.sigle)::varchar) as sigle,
            mode() within group (order by trim(i.libelle)::varchar) as libelle,
            mode() within group (order by i.code) as code
        from pre_structure i
             join v_structure_doublon v on v.id = i.id and v.npd = p_npd
        where i.histo_destruction is null
        group by v.npd;
end;
$$;


create or replace function substit_create_substituant_structure(data record) returns bigint
    language plpgsql
as
$$declare
    substituant_id bigint;
begin
    --
    -- Création d'un enregistrement "susbtituant", càd se substituant à plusieurs enregistrements considérés en doublon,
    -- à partir des valeurs d'attributs spécifiées.
    --

    raise notice 'Insertion du substituant à partir des données %', data;
    insert into structure (id,
                          source_id,
                          source_code,
                          histo_createur_id,
                          type_structure_id,
                          sigle,
                          libelle,
                          code,
                          est_substituant,
                          est_substituant_modifiable)
    select nextval('structure_id_seq') as id,
           app_source_id() as source_id,
           app_source_source_code() as source_code,
           app_utilisateur_id(),
           data.type_structure_id,
           data.sigle,
           data.libelle,
           data.code,
           true,
           default
    returning id into substituant_id;

    raise notice '=> Substituant %', substituant_id;

    return substituant_id;
end
$$;


create or replace function substit_update_substituant_structure(p_substituant_id bigint, data record) returns void
    language plpgsql
as
$$begin
    --
    -- Mise à jour des attributs de la structure substituante spécifiée, à partir des valeurs spécifiées.
    --

    update structure
    set histo_modification = current_timestamp,
        histo_modificateur_id = app_utilisateur_id(),
        type_structure_id = data.type_structure_id,
        sigle = data.sigle,
        libelle = data.libelle,
        code = data.code
    where id = p_substituant_id;
end
$$;


--drop function substit_create_all_substitutions_structure
create or replace function substit_create_all_substitutions_structure(limite integer default null) returns smallint
    language plpgsql
as
$$declare
    v_npd varchar(256);
    v_pre_count smallint;
    v_count smallint = 0;
    v_data record;
    v_structure_substituant_id bigint;
    v_structure_substitue record;
begin
    --
    -- Fonction de créations de N substitutions parmi toutes les substitutions possibles.
    --

    -- nombre de nouvelles substitutions possibles
    select count(distinct npd) into v_pre_count from v_structure_doublon v where v.id not in (
        select from_id from structure_substit where histo_destruction is null
    );
    raise notice '>>> Nombre de nouvelles substitutions possibles : %', v_pre_count;

    for v_npd in select distinct npd from v_structure_doublon v where v.id not in (
        select from_id from structure_substit where histo_destruction is null
    ) order by npd loop
            raise notice '>>> Substitution % sur %', v_count+1, v_pre_count;

            v_data = substit_fetch_data_for_substituant_structure(v_npd);
            if v_data is null then
                raise exception 'Anomalie : aucune donnée trouvée pour le NPD % !', v_npd;
            end if;
            v_structure_substituant_id = substit_create_substituant_structure(v_data);
            for v_structure_substitue in select * from v_structure_doublon v where npd = v_npd loop
                    perform substit_add_to_substitution('structure', v_structure_substitue.id, v_npd, v_structure_substituant_id);
                end loop;
            v_count = v_count + 1;

            exit when limite is not null and v_count >= limite;
        end loop;

    raise notice '>> Nombre de substitutions créées : %', v_count;
    return v_count;
end;
$$;
