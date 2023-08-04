--
-- Substitutions
--

--=============================== DOCTORANT ================================-

alter table tmp_doctorant add column code_apprenant_in_source varchar(128);

create index doctorant_ine_index on doctorant (ine);

-- sauvegarde table doctorant
create table doctorant_sav as select * from doctorant;

-- nouvelle table PRE_DOCTORANT
create table pre_doctorant (like doctorant including all);
insert into pre_doctorant select * from doctorant;
alter table pre_doctorant add column code_apprenant_in_source varchar(128);
alter table pre_doctorant add column npd_force varchar(256);
alter table pre_doctorant add constraint pre_doctorant_source_fk foreign key (source_id) references source on delete cascade;
alter table pre_doctorant add constraint pre_doctorant_hc_fk foreign key (histo_createur_id) references utilisateur on delete cascade;
alter table pre_doctorant add constraint pre_doctorant_hm_fk foreign key (histo_modificateur_id) references utilisateur on delete cascade;
alter table pre_doctorant add constraint pre_doctorant_hd_fk foreign key (histo_destructeur_id) references utilisateur on delete cascade;
create sequence if not exists pre_doctorant_id_seq owned by pre_doctorant.id;
alter table pre_doctorant alter column id set default nextval('pre_doctorant_id_seq');
select setval('pre_doctorant_id_seq', (select max(id) from pre_doctorant));

--drop table doctorant_substit cascade;
create table doctorant_substit
(
    id bigserial not null primary key,
    from_id bigint not null constraint doctorant_substit_from_fk references pre_doctorant on delete no action, -- 'no action' requis car trigger sur PRE_DOCTORANT
    to_id bigint not null constraint doctorant_substit_to_fk references doctorant on delete no action, -- idem
    npd varchar(256) not null,
    histo_creation timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modification timestamp,
    histo_destruction timestamp,
    histo_createur_id bigint constraint doctorant_substit_createur_fk references utilisateur,
    histo_modificateur_id bigint constraint doctorant_substit_modificateur_fk references utilisateur,
    histo_destructeur_id bigint constraint doctorant_substit_destructeur_fk references utilisateur
);
create unique index doctorant_substit_unique_idx on doctorant_substit(from_id) where histo_destruction is null;
create unique index doctorant_substit_unique_hist_idx on doctorant_substit(from_id, histo_destruction) where histo_destruction is not null;


--drop view v_diff_pre_doctorant;
--drop view src_pre_doctorant;
create or replace view src_pre_doctorant as
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
             JOIN individu i ON i.source_code = tmp.individu_id;


drop view if exists v_diff_doctorant;
drop view if exists src_doctorant;
create or replace view src_doctorant as
    select pre.id,
           coalesce(isub.to_id, pre.individu_id) as individu_id,
           coalesce(es.id, pre.etablissement_id) as etablissement_id,
           pre.source_code,
           pre.source_id,
           pre.ine,
           pre.code_apprenant_in_source
    from pre_doctorant pre
    join pre_etablissement pe on pre.etablissement_id = pe.id
    left join individu_substit isub on isub.from_id = pre.individu_id and isub.histo_destruction is null
    left join structure_substit ssub on ssub.from_id = pe.structure_id and ssub.histo_destruction is null
    left join etablissement es on es.structure_id = ssub.to_id
    where pre.histo_destruction is null and not exists (
        select * from doctorant_substit where histo_destruction is null and from_id = pre.id
    );


--
-- Trigger sur la table PRE_DOCTORANT se déclenchant en cas d'insertion ou de mise à jour de l'un des attributs suivants :
--   - attributs participant au NPD (ine)
--   - NPD forcé.
drop trigger if exists substit_trigger_pre_doctorant on pre_doctorant;
create trigger substit_trigger_pre_doctorant
    after insert
        or delete
        or update of
            ine, -- pour entrer ou sortir d'une substitution éventuelle (NPD)
            --
            individu_id, etablissement_id, code_apprenant_in_source, -- pour mettre à jour le substituant éventuel
            --
            npd_force, -- pour réagir à une demande de substitution forcée
            histo_destruction, -- pour réagir à l'historisation/restauration d'un enregsitrement
            source_id -- pour réagir au changement de source (source application => source compatible, et vice-versa)
    on pre_doctorant
    for each row
execute procedure substit_trigger_on_pre_fct('doctorant');

--
-- Trigger sur la table DOCTORANT se déclenchant en cas d'insertion d'un enregistrement potentiellement substituant,
-- en vue de remplacer partout où c'est nécessaire les valeurs des clés étrangères par l'id du substituant.
--
drop trigger if exists substit_trigger_on_doctorant_substit on doctorant_substit;
create trigger substit_trigger_on_doctorant_substit
    after insert
    or update of histo_destruction
    or delete
    on doctorant_substit
    for each row
execute procedure substit_trigger_on_substit_fct('doctorant');


--drop function substit_npd_doctorant cascade
create or replace function substit_npd_doctorant(pre_record pre_doctorant) returns varchar
    language plpgsql
as
$$declare
    v_npd_individu varchar(256);
begin
    --
    -- Fonction de calcul du "NPD" d'un doctorant.
    --
    -- Important : Le NPD d'un doctorant "inclut" celui de l'individu lié. Cela permet de garantir qu'un doctorant
    -- ne peut être considéré comme un doublon si son individu lié ne l'est pas lui-même.
    --
    -- Attention !
    -- Modifier le calcul du NPD n'est pas une mince affaire car cela remet en question les substitutions existantes
    -- définies dans la table 'xxxx_substit'.
    -- > Dans le cas où cela ne change rien du tout aux substitutions existantes, il faudra tout de même :
    --   - mettre à jour les valeurs dans la colonne 'npd' de la table 'xxxx_substit' en faisant appel
    --     à la fonction 'substit_npd_xxxx()';
    --   - mettre à jour manuellement les valeurs dans la colonne 'npd_force" de la table 'pre_xxxx'.
    -- > Dans le cas où cela invalide des substitutions existantes, il faudra :
    --   - historiser les substitutions concernées dans la table 'xxxx_substit' ;
    --   - mettre à jour manuellement les valeurs dans la colonne 'npd_force" de la table 'pre_xxxx'.
    --

    select substit_npd_individu(i.*) into v_npd_individu from pre_individu i where id = pre_record.individu_id;

    return v_npd_individu || ',' || pre_record.ine;
end;
$$;


--
-- Vue retournant les doctorants en doublon au regard de leur NPD.
--
-- Rappel : Les doctorants en doublons ne sont pas recherchés dans la source correspondant à l'application.
--
--drop view v_doctorant_doublon;
create or replace view v_doctorant_doublon as
with doctorants_npd as (
    select coalesce(pre.npd_force, substit_npd_doctorant(pre.*)) as npd, pre.id, pre.ine
    from pre_doctorant pre
    join pre_individu prei on prei.id = pre.individu_id and prei.histo_destruction is null
    where pre.histo_destruction is null and pre.source_id <> app_source_id()
), npds(npd) as (
    select npd, count(*)
    from doctorants_npd
    group by npd
    having count(*) > 1
)
select d.id, d.ine, npds.npd
from npds, doctorants_npd d
where d.npd = npds.npd
order by d.ine
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
    -- Détermination des meilleures valeurs d'attributs des doctorants en doublon en vue de les affecter au
    -- doctorant substituant.
    --
    -- Pour chaque attribut, la stratégie de choix de la "meilleure" valeur est la fonction 'mode()'
    -- (cf. https://www.postgresql.org/docs/current/functions-aggregate).
    --
    -- Rappel : les doctorants en doublon sont les doctorants ayant le même NPD et n'appartenant pas à la source
    -- correspondnant à l'application.
    --
    -- Important : Une jointure est faite ici avec INDIVIDU_SUBSTIT pour obtenir le cas échéant l'id de l'individu substituant.
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
        from pre_doctorant pre
                 join v_doctorant_doublon v on v.id = pre.id and v.npd = p_npd
                 left join individu_substit sub on sub.from_id = pre.individu_id and sub.histo_destruction is null
        where pre.histo_destruction is null
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
    -- Création d'un doctorant dit "susbtituant", càd se substituant à plusieurs doctorants considéré en doublon,
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
    -- Mise à jour des attributs du doctorant substituant spécifié, à partir des valeurs spécifiées.
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
    v_doctorant_substituant_id bigint;
    v_doctorant_substitue record;
begin
    --
    -- Fonction de créations de N substitutions parmi toutes les substitutions possibles.
    --

    -- nombre de nouvelles substitutions possibles
    select count(distinct npd) into v_pre_count from v_doctorant_doublon v where v.id not in (
        select from_id from doctorant_substit where histo_destruction is null
    );
    raise notice '>>> Nombre de nouvelles substitutions possibles : %', v_pre_count;

    for v_npd in select distinct npd from v_doctorant_doublon v where v.id not in (
        select from_id from doctorant_substit where histo_destruction is null
    ) order by npd loop
            raise notice '>>> Substitution % sur %', v_count+1, v_pre_count;

            v_data = substit_fetch_data_for_substituant_doctorant(v_npd);
            if v_data is null then
                raise exception 'Anomalie : aucune donnée trouvée pour le NPD % !', v_npd;
            end if;
            v_doctorant_substituant_id = substit_create_substituant_doctorant(v_data);
            for v_doctorant_substitue in select * from v_doctorant_doublon v where npd = v_npd loop
                    perform substit_add_to_substitution('doctorant', v_doctorant_substitue.id, v_npd, v_doctorant_substituant_id);
                end loop;
            v_count = v_count + 1;

            exit when limite is not null and v_count >= limite;
        end loop;

    raise notice '>> Nombre de substitutions créées : %', v_count;
    return v_count;
end;
$$;

