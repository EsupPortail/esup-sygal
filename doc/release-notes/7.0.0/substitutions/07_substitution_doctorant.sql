--
-- Substitutions
--

--=============================== DOCTORANT ================================-

--
-- Vue listant les clés étrangères (FK) pointant vers 'doctorant'
-- dont la valeur doit être remplacée par l'id substituant éventuel.
--
-- drop view v_substit_foreign_keys_doctorant;
create or replace view v_substit_foreign_keys_doctorant as
select * from v_substit_foreign_keys
where target_table = 'doctorant'
  and source_table <> 'doctorant'
  and source_table <> 'substit_doctorant'
;


--
-- Mise à jour table doctorant
--
alter table doctorant add column if not exists npd_force varchar(256);
alter table doctorant add column if not exists est_substituant_modifiable bool default true not null;
comment on column doctorant.est_substituant_modifiable is 'Indique si ce substituant (le cas échéant) peut être mis à jour à partir des attributs des substitués';
create index if not exists doctorant_ine_index on doctorant (ine);

alter table doctorant add column if not exists synchro_undelete_enabled boolean default true not null;
alter table doctorant add column if not exists synchro_update_on_deleted_enabled boolean default false not null;

-- sauvegarde table doctorant
create table sav__doctorant as select * from doctorant;

--drop table substit_doctorant cascade;
create table substit_doctorant
(
    id bigserial not null primary key,
    from_id bigint not null constraint doctorant_substit_from_fk references doctorant on delete no action, -- 'no action' requis car trigger sur DOCTORANT
    to_id bigint not null constraint doctorant_substit_to_fk references doctorant on delete no action, -- idem
    npd varchar(256) not null,
    histo_creation timestamp default ('now'::text)::timestamp without time zone not null,
    histo_createur_id bigint constraint doctorant_substit_createur_fk references utilisateur,
    histo_modification timestamp,
    histo_modificateur_id bigint constraint doctorant_substit_modificateur_fk references utilisateur/*,
    histo_destruction timestamp, -- les substit_xxx ne sont pas historisés mais supprimés
    histo_destructeur_id bigint constraint doctorant_substit_destructeur_fk references utilisateur*/
);
-- create unique index doctorant_substit_unique_idx on substit_doctorant(from_id) where histo_destruction is null;
-- create unique index doctorant_substit_unique_hist_idx on substit_doctorant(from_id, histo_destruction) where histo_destruction is not null;
create unique index doctorant_substit_unique_idx on substit_doctorant(from_id);

drop view if exists v_diff_doctorant;
drop view if exists src_doctorant;
create or replace view src_doctorant as
    with pre as (
        SELECT NULL::bigint AS id,
               tmp.source_code,
               tmp.code_apprenant_in_source,
               tmp.ine,
               src.id     AS source_id,
               i.id       AS individu_id,
               e.id       AS etablissement_id
        FROM tmp_doctorant tmp
                 JOIN source src ON src.id = tmp.source_id
                 JOIN etablissement e ON e.id = src.etablissement_id
                 JOIN individu i ON i.source_code = tmp.individu_id
    )
    select pre.id,
           coalesce(isub.to_id, pre.individu_id) as individu_id,
           coalesce(esub.to_id, pre.etablissement_id) as etablissement_id,
           pre.source_code,
           pre.source_id,
           pre.ine,
           pre.code_apprenant_in_source
    from pre
    left join substit_individu isub on isub.from_id = pre.individu_id
    left join substit_etablissement esub on esub.from_id = pre.etablissement_id;

drop trigger if exists substit_trigger_doctorant on doctorant;
create trigger substit_trigger_doctorant
    after insert
        or delete
        or update of
            individu_id, ine, -- pour entrer ou sortir d'une substitution éventuelle (NPD)
            --
            etablissement_id, code_apprenant_in_source, -- pour mettre à jour le substituant éventuel
            --
            npd_force, -- pour réagir à une demande de substitution forcée
            histo_destruction, -- pour réagir à l'historisation/restauration d'un enregsitrement
            source_id -- pour réagir au changement de source (source application => source compatible, et vice-versa)
    on doctorant
    for each row
    when (pg_trigger_depth() < 1)
execute procedure substit_trigger_fct('doctorant');

--
-- Trigger se déclenchant en cas d'insertion d'un enregistrement potentiellement substituant,
-- en vue de remplacer partout où c'est nécessaire les valeurs des clés étrangères par l'id du substituant.
--
drop trigger if exists substit_trigger_on_doctorant_substit on substit_doctorant;
drop trigger if exists substit_trigger_on_substit_doctorant on substit_doctorant;
create trigger substit_trigger_on_substit_doctorant
    after insert
    --or update of histo_destruction
    or delete
    on substit_doctorant
    for each row
execute procedure substit_trigger_on_substit_fct('doctorant');

alter table doctorant enable trigger substit_trigger_doctorant;
alter table substit_doctorant enable trigger substit_trigger_on_substit_doctorant;


--drop function substit_npd_doctorant cascade
create or replace function substit_npd_doctorant(doctorant doctorant) returns varchar
    language plpgsql
as
$$declare
    v_npd_individu varchar(256);
begin
    --
    -- Fonction de calcul du "NPD".
    --
    -- Important : Le NPD d'un doctorant "inclut" celui de l'individu lié. Cela permet de garantir qu'un doctorant
    -- ne peut être considéré comme un doublon si son individu lié ne l'est pas lui-même.
    --
    -- Attention !
    -- Modifier le calcul du NPD n'est pas une mince affaire car cela remet en question les substitutions existantes
    -- définies dans la table 'xxxx_substit'.
    -- > Dans les 2 cas qui suivent, il faudra absolument désactiver au préalable les triggers suivants :
    --   - substit_trigger_xxxx
    --   - substit_trigger_on_xxxx_substit
    -- > Dans le cas où cela ne change rien du tout aux substitutions existantes, il faudra tout de même :
    --   - mettre à jour les valeurs dans la colonne 'npd' de la table 'xxxx_substit' en faisant appel
    --     à la fonction 'substit_npd_xxxx()';
    --   - mettre à jour manuellement les valeurs dans la colonne 'npd_force" de la table 'xxxx'.
    -- > Dans le cas où cela invalide des substitutions existantes, il faudra :
    --   - historiser les substitutions concernées dans la table 'xxxx_substit' ;
    --   - mettre à jour manuellement les valeurs dans la colonne 'npd_force" de la table 'xxxx'.
    --

    select substit_npd_individu(i.*) into v_npd_individu from individu i where id = doctorant.individu_id;

    return v_npd_individu || ',' || doctorant.ine;
end;
$$;


--
-- Vue retournant les enregistrements en doublon au regard de leur NPD.
--
-- NB : Les enregistrements en doublons ne sont pas recherchés dans la source correspondant à l'application.
-- NB : Les historisés ne sont pas écartés puisqu'ils peuvent être des enregistrements déjà subsitués.
--
--drop view v_doctorant_doublon;
create or replace view v_doctorant_doublon as
with doctorants_npd as (
    select coalesce(pre.npd_force, substit_npd_doctorant(pre.*)) as npd, pre.id, pre.ine
    from doctorant pre
    join individu prei on prei.id = pre.individu_id
    where /*pre.histo_destruction is null and*/ pre.source_id <> app_source_id()
), npds(npd) as (
    select npd, count(*)
    from doctorants_npd
    group by npd
    having count(*) > 1
)
select d.id, d.ine, npds.npd
from npds, doctorants_npd d
where d.npd = npds.npd
;

create or replace function substit_fetch_data_for_substituant_doctorant(p_npd varchar)
    returns table
            (
                individu_id bigint,
                etablissement_id bigint,
                ine varchar,
                code_apprenant_in_source varchar
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
    -- Important : Une jointure est faite ici avec substit_individu pour obtenir le cas échéant l'id de l'individu substituant.
    -- On rappelle que le nécessaire est fait en amont pour ne pas considérer des doctorants commes des doublons
    -- alors que leur individu lié (via individu_id) n'est pas lui-même considéré comme doublon.
    --

    raise notice 'Calcul des meilleures valeurs d''attributs parmi les doublons dont le NPD est %...', p_npd;

    return query
        select
            mode() within group (order by coalesce(sub.to_id, pre.individu_id)) as individu_id, -- éventuel individu substituant
            mode() within group (order by pre.etablissement_id) as etablissement_id, -- Quel etab ? La COMUE (éventuelle) ?
            mode() within group (order by pre.ine) as ine,
            mode() within group (order by pre.code_apprenant_in_source) as code_apprenant_in_source
        from doctorant pre
                 join v_doctorant_doublon v on v.id = pre.id and v.npd = p_npd
                 left join substit_individu sub on sub.from_id = pre.individu_id --and sub.histo_destruction is null
        group by v.npd;
end;
$$;


create or replace function substit_create_substituant_doctorant(data record) returns bigint
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
    insert into doctorant (id,
                           source_id,
                           source_code,
                           histo_createur_id,
                           individu_id,
                           ine,
                           code_apprenant_in_source,
                           etablissement_id)
    select nextval('doctorant_id_seq') as id,
           app_source_id() as source_id,
           app_source_source_code() as source_code,
           app_utilisateur_id(),
           data.individu_id,
           data.ine,
           data.code_apprenant_in_source,
           data.etablissement_id
    returning id into substituant_id;

    raise notice '=> Substituant %', substituant_id;

    return substituant_id;
end
$$;


create or replace function substit_update_substituant_doctorant(p_substituant_id bigint, data record) returns void
    language plpgsql
as
$$begin
    --
    -- Mise à jour des attributs de l'enregistrement substituant spécifié, à partir des valeurs spécifiées.
    --

    update doctorant
    set histo_modification = current_timestamp,
        histo_modificateur_id = app_utilisateur_id(),
        individu_id = data.individu_id,
        code_apprenant_in_source = data.code_apprenant_in_source,
        ine = data.ine,
        etablissement_id = data.etablissement_id
    where id = p_substituant_id;
end
$$;


--drop function substit_create_all_substitutions_doctorant
create or replace function substit_create_all_substitutions_doctorant(limite integer default null) returns smallint
    language plpgsql
as
$$declare
    v_npd varchar(256);
    v_pre_count smallint;
    v_count smallint = 0;
    v_data record;
    v_substituant_id bigint;
    v_substitue record;
begin
    --
    -- Fonction de créations de N substitutions parmi toutes les substitutions possibles.
    --

    -- nombre de nouvelles substitutions possibles
    select count(distinct npd) into v_pre_count from v_doctorant_doublon v where v.id not in (
        select from_id from substit_doctorant --where histo_destruction is null
    );
    raise notice '>>> Nombre de nouvelles substitutions possibles : %', v_pre_count;

    for v_npd in select distinct npd from v_doctorant_doublon v where v.id not in (
        select from_id from substit_doctorant --where histo_destruction is null
    ) order by npd loop
            raise notice '>>> Substitution % sur %', v_count+1, v_pre_count;

            v_data = substit_fetch_data_for_substituant_doctorant(v_npd);
            if v_data is null then
                raise exception 'Anomalie : aucune donnée trouvée pour le NPD % !', v_npd;
            end if;
            v_substituant_id = substit_create_substituant_doctorant(v_data);
            for v_substitue in select * from v_doctorant_doublon v where npd = v_npd loop
                    perform substit_add_to_substitution('doctorant', v_substitue.id, v_npd, v_substituant_id);
                    perform substit_remove_substitue('doctorant', v_substitue.id, v_substituant_id);
                end loop;
            v_count = v_count + 1;

            exit when limite is not null and v_count >= limite;
        end loop;

    raise notice '>> Nombre de substitutions créées : %', v_count;
    return v_count;
end;
$$;