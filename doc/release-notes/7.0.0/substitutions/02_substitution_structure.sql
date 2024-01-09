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
  and source_table <> 'substit_structure'
  and not (
    -- NB : pas de remplacement de ces FK car, le NPD d'un etablissement/ecole_doct/unite_rech étant égal à celui de sa
    -- structure liée, la substitution de la structure sera systématiquement suivie de celle de l'etablissement/ecole_doct/unite_rech.
    source_table = 'etablissement' and fk_column = 'structure_id' or
    source_table = 'ecole_doct' and fk_column = 'structure_id' or
    source_table = 'unite_rech' and fk_column = 'structure_id'
  )
;


--
-- Mise à jour table STRUCTURE
--
alter table structure add column if not exists npd_force varchar(256);
alter table structure add column if not exists est_substituant_modifiable bool default true not null;
comment on column structure.est_substituant_modifiable is 'Indique si ce substituant (le cas échéant) peut être mis à jour à partir des attributs des substitués';
alter table structure alter column code set not null;
alter table structure alter column code drop default;
create index if not exists structure_code_index on structure (code);

alter table structure add column if not exists synchro_undelete_enabled boolean default true not null;
alter table structure add column if not exists synchro_update_on_deleted_enabled boolean default false not null;

-- sauvegardes tables
create table sav__structure as select * from structure;
create table sav__structure_substit as select * from structure_substit;

alter table structure_substit rename to substit_structure;
delete from substit_structure where histo_destruction is not null;
alter table substit_structure drop column histo_destructeur_id; -- les substit_xxx ne sont pas historisés mais supprimés
alter table substit_structure drop column histo_destruction;
alter table substit_structure rename column from_structure_id to from_id;
alter table substit_structure rename column to_structure_id to to_id;
alter table substit_structure alter column histo_modification drop not null;
alter table substit_structure alter column histo_modification drop default;
alter table substit_structure add npd varchar(256);
alter table substit_structure drop constraint str_substit_str_from_fk;
alter table substit_structure add constraint str_substit_str_from_fk foreign key (from_id) references structure (id);
alter table substit_structure drop constraint str_substit_str_to_fk;
alter table substit_structure add constraint str_substit_str_to_fk foreign key (to_id) references structure;
create sequence if not exists structure_substit_id_seq owned by substit_structure.id;
alter table substit_structure alter column id set default nextval('structure_substit_id_seq');
drop index if exists str_substit_unique;
-- create unique index structure_substit_unique_idx on substit_structure(from_id) where histo_destruction is null;
-- create unique index structure_substit_unique_hist_idx on substit_structure(from_id, histo_destruction) where histo_destruction is not null;
create unique index structure_substit_unique_idx on substit_structure(from_id);

update structure s set synchro_update_on_deleted_enabled = true from substit_structure ss where s.id = ss.from_id;
update structure s set synchro_undelete_enabled = false from substit_structure ss where s.id = ss.from_id;


drop view if exists v_diff_structure;
drop view if exists src_structure;
create or replace view src_structure as
    with pre as (
        select null::bigint as id,
               tmp.source_code,
               ltrim(substr(tmp.source_code::text, strpos(tmp.source_code::text, '::'::text)), ':'::text) AS code,
               src.id AS source_id,
               ts.id AS type_structure_id,
               tmp.sigle,
               tmp.libelle
        FROM tmp_structure tmp
                 JOIN type_structure ts ON ts.code = tmp.type_structure_id
                 JOIN source src ON src.id = tmp.source_id
    )
    select *
    from pre;

drop trigger if exists substit_trigger_structure on structure;
create trigger substit_trigger_structure
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
    on structure
    for each row
    when (pg_trigger_depth() < 1)
execute procedure substit_trigger_fct('structure');

--
-- Trigger se déclenchant en cas d'insertion d'un enregistrement potentiellement substituant,
-- en vue de remplacer partout où c'est nécessaire les valeurs des clés étrangères par l'id du substituant.
--
drop trigger if exists substit_trigger_on_structure_substit on substit_structure;
drop trigger if exists substit_trigger_on_substit_structure on substit_structure;
create trigger substit_trigger_on_substit_structure
    after insert
--     or update of histo_destruction
    or delete
    on substit_structure
    for each row
execute procedure substit_trigger_on_substit_fct('structure');

alter table structure enable trigger substit_trigger_structure;
alter table substit_structure enable trigger substit_trigger_on_substit_structure;


create or replace function substit_npd_structure(structure structure) returns varchar
    language plpgsql
as
$$declare
    v_code_type_struct varchar(64);
begin
    --
    -- Fonction de calcul du "NPD".
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
    --   - substit_trigger_xxxx
    --   - substit_trigger_on_xxxx_substit
    -- > Dans le cas où cela ne change rien du tout aux substitutions existantes, il faudra tout de même :
    --   - mettre à jour les valeurs dans la colonne 'npd' de la table 'xxxx_substit' en faisant appel
    --     à la présente fonction ;
    --   - mettre à jour manuellement les valeurs dans la colonne 'npd_force" de la table 'xxxx'.
    -- > Dans le cas où cela invalide des substitutions existantes, il faudra :
    --   - historiser les substitutions concernées dans la table 'xxxx_substit' ;
    --   - mettre à jour manuellement les valeurs dans la colonne 'npd_force" de la table 'xxxx'.
    --

    select code into v_code_type_struct from type_structure where id = structure.type_structure_id;

    return v_code_type_struct || ',' || structure.code;
end;
$$;


--
-- Vue retournant les enregistrements en doublon au regard de leur NPD.
--
-- NB : Les enregistrements en doublons ne sont pas recherchés dans la source correspondant à l'application.
-- NB : Les historisés ne sont pas écartés puisqu'ils peuvent être des enregistrements déjà subsitués.
--
--drop view v_structure_doublon;
create or replace view v_structure_doublon as
with structures_npd as (
    select coalesce(npd_force, substit_npd_structure(pre.*)) as npd, id, code
    from structure pre
    where /*histo_destruction is null and*/ source_id <> app_source_id()
), npds(npd) as (
    select npd, count(*)
    from structures_npd
    group by npd
    having count(*) > 1
)
select i.id, i.code, npds.npd
from npds, structures_npd i
where i.npd = npds.npd
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
    -- Détermination des meilleures valeurs d'attributs des enregistrements en doublon en vue de les affecter à
    -- l'enregistrement substituant.
    --
    -- Pour chaque attribut, la stratégie de choix de la "meilleure" valeur est la fonction 'mode()'
    -- (cf. https://www.postgresql.org/docs/current/functions-aggregate).
    --
    -- NB : les enregistrements en doublon sont ceux ayant le même NPD et n'appartenant pas à la source
    -- correspondnant à l'application.
    -- NB : Les historisés ne sont pas écartés puisqu'ils peuvent être des enregistrements déjà subsitués.
    --

    raise notice 'Calcul des meilleures valeurs d''attributs parmi les doublons dont le NPD est %...', p_npd;

    return query
        select
            mode() within group (order by i.type_structure_id) as type_structure_id,
            mode() within group (order by trim(i.sigle)::varchar) as sigle,
            mode() within group (order by trim(i.libelle)::varchar) as libelle,
            mode() within group (order by i.code) as code
        from structure i
             join v_structure_doublon v on v.id = i.id and v.npd = p_npd
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
                          code)
    select nextval('structure_id_seq') as id,
           app_source_id() as source_id,
           app_source_source_code() as source_code,
           app_utilisateur_id(),
           data.type_structure_id,
           data.sigle,
           data.libelle,
           data.code
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
    -- Mise à jour des attributs de l'enregistrement substituant spécifié, à partir des valeurs spécifiées.
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
    -- Cette fonction crée N nouvelles substitutions parmi les substitutions possibles.
    --

    -- nombre de nouvelles substitutions possibles
    select count(distinct npd) into v_pre_count from v_structure_doublon v where v.id not in (
        select from_id from substit_structure --where histo_destruction is null
    );
    raise notice '>>> Nombre de nouvelles substitutions possibles : %', v_pre_count;

    for v_npd in select distinct npd from v_structure_doublon v where v.id not in (
        select from_id from substit_structure --where histo_destruction is null
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