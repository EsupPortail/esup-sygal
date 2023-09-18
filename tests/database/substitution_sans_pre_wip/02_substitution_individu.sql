--
-- Substitutions
--

--=============================== INDIVIDU ================================-

--
-- Vue listant les clés étrangères (FK) pointant vers 'individu'
-- dont la valeur doit être remplacée par l'id substituant éventuel.
--
-- drop view v_substit_foreign_keys_individu;
create or replace view v_substit_foreign_keys_individu as
    select * from v_substit_foreign_keys
    where target_table = 'individu'
      and source_table <> 'individu'
      and source_table <> 'individu_substit'
      and not (
          source_table = 'doctorant' and fk_column = 'individu_id'
      )
;


--
-- Mise à jour table INDIVIDU
--
-- nom patronymique not nullable
update individu set nom_patronymique = nom_usuel where nom_patronymique is null;
alter table individu alter nom_patronymique set not null;
create index individu_nom_patronymique_index on individu (nom_patronymique);
create index individu_prenom1_index on individu (prenom1);
create index individu_date_naissance_index on individu (date_naissance);

-- sauvegarde table individu
create table individu_sav as select * from individu;


--drop table individu_substit cascade;
create table individu_substit
(
    id bigserial not null primary key,
    from_id bigint not null constraint individu_substit_from_fk references individu on delete no action, -- 'no action' requis car trigger sur INDIVIDU
    to_id bigint not null constraint individu_substit_to_fk references individu on delete no action, -- idem
    npd varchar(256) not null,
    histo_creation timestamp default ('now'::text)::timestamp without time zone not null,
    histo_modification timestamp,
    histo_destruction timestamp,
    histo_createur_id bigint constraint individu_substit_createur_fk references utilisateur,
    histo_modificateur_id bigint constraint individu_substit_modificateur_fk references utilisateur,
    histo_destructeur_id bigint constraint individu_substit_destructeur_fk references utilisateur
);
create unique index individu_substit_unique_idx on individu_substit(from_id) where histo_destruction is null;
create unique index individu_substit_unique_hist_idx on individu_substit(from_id, histo_destruction) where histo_destruction is not null;

--drop view v_diff_individu;
--drop view src_individu;
create or replace view src_individu as
    select null::bigint        as id,
           tmp.source_code,
           src.id              as source_id,
           tmp.type,
           tmp.supann_id,
           tmp.civ             as civilite,
           tmp.lib_nom_usu_ind as nom_usuel,
           tmp.lib_nom_pat_ind as nom_patronymique,
           tmp.lib_pr1_ind     as prenom1,
           tmp.lib_pr2_ind     as prenom2,
           tmp.lib_pr3_ind     as prenom3,
           tmp.email,
           tmp.dat_nai_per     as date_naissance,
           tmp.lib_nat         as nationalite,
           p.id                as pays_id_nationalite
    from tmp_individu tmp
        join source src on src.id = tmp.source_id
        left join pays p on p.code_pays_apogee::text = tmp.cod_pay_nat::text
    where not exists (
        select id
        from individu_substit substit
        join individu substitue on substit.from_id = substitue.id
        where substit.histo_destruction is null and tmp.source_code = substitue.source_code
    );


--
-- Trigger sur la table INDIVIDU se déclenchant en cas d'insertion ou de mise à jour de l'un des attributs suivants :
--   - attributs participant au NPD (nom_patronymique, prenom1, date_naissance)
--   - NPD forcé.
drop trigger if exists substit_trigger_individu on individu;
create trigger substit_trigger_individu
    after insert
        or delete
        or update of
            nom_patronymique, prenom1, date_naissance, -- pour entrer ou sortir d'une substitution éventuelle (NPD)
            --
            type, civilite, nom_usuel, prenom2, prenom3, email, nationalite,
            supann_id, etablissement_id, pays_id_nationalite, -- pour mettre à jour le substituant éventuel
            --
            npd_force, -- pour réagir à une demande de substitution forcée
            histo_destruction, -- pour réagir à l'historisation/restauration d'un enregsitrement
            source_id -- pour réagir au changement de source (source application => source compatible, et vice-versa)
    on individu
    for each row
execute procedure substit_trigger_fct('individu');

--
-- Trigger sur la table INDIVIDU se déclenchant en cas d'insertion d'un enregistrement potentiellement substituant,
-- en vue de remplacer partout où c'est nécessaire les valeurs des clés étrangères par l'id du substituant.
--
drop trigger if exists substit_trigger_on_substit_individu on individu_substit;
create trigger substit_trigger_on_substit_individu
    after insert
    or update of histo_destruction
    or delete
    on individu_substit
    for each row
execute procedure substit_trigger_on_substit_fct('individu');


create or replace function substit_npd_individu(individu individu) returns varchar
    language plpgsql
as
$$begin
    --
    -- Fonction de calcul du "NPD" d'un individu.
    --
    -- Attention !
    -- Modifier le calcul du NPD n'est pas une mince affaire car cela remet en question les substitutions existantes
    -- définies dans la table 'xxxx_substit'.
    -- > Dans le cas où cela ne change rien du tout aux substitutions existantes, il faudra tout de même :
    --   - mettre à jour les valeurs dans la colonne 'npd' de la table 'xxxx_substit' en faisant appel
    --     à la présente fonction;
    --   - mettre à jour manuellement les valeurs dans la colonne 'npd_force" de la table 'xxxx'.
    -- > Dans le cas où cela invalide des substitutions existantes, il faudra :
    --   - historiser les substitutions concernées dans la table 'xxxx_substit' ;
    --   - mettre à jour manuellement les valeurs dans la colonne 'npd_force" de la table 'xxxx'.
    --

    return normalized_string(trim(individu.nom_patronymique)) || '_' ||
           normalized_string(trim(individu.prenom1)) || '_' ||
           normalized_string(coalesce(date(individu.date_naissance)::varchar, ''));
end;
$$;


--
-- Vue retournant les individus en doublon au regard de leur NPD.
--
-- Rappel : Les individus en doublons ne sont pas recherchés dans la source correspondant à l'application.
--
--drop view v_individu_doublon;
create or replace view v_individu_doublon as
with individus_npd as (
    select coalesce(npd_force, substit_npd_individu(pre.*)) as npd, id, source_code, nom_patronymique, prenom1, date_naissance
    from individu pre
    where histo_destruction is null and source_id <> app_source_id()
), npds(npd) as (
    select npd, count(*)
    from individus_npd
    group by npd
    having count(*) > 1
)
select i.id, i.source_code, i.nom_patronymique, i.prenom1, i.date_naissance, npds.npd
from npds, individus_npd i
where i.npd = npds.npd
order by i.nom_patronymique, i.prenom1
;

--drop function substit_fetch_data_for_substituant_individu;
create or replace function substit_fetch_data_for_substituant_individu(p_npd varchar)
    returns table
            (
                type                varchar,
                civilite            varchar,
                nom_patronymique    varchar,
                nom_usuel           varchar,
                prenom1             varchar,
                prenom2             varchar,
                prenom3             varchar,
                email               varchar,
                date_naissance      timestamp,
                nationalite         varchar,
                supann_id           varchar,
                etablissement_id    bigint,
                pays_id_nationalite bigint
            )
    language plpgsql
as
$$begin
    --
    -- Détermination des meilleures valeurs d'attributs des individus en doublon en vue de les affecter à
    -- l'individu substituant.
    --
    -- Pour chaque attribut, la stratégie de choix de la "meilleure" valeur est la fonction 'mode()'
    -- (cf. https://www.postgresql.org/docs/current/functions-aggregate).
    --
    -- Rappel : les individus en doublon sont les individus ayant le même NPD et n'appartenant pas à la source
    -- correspondnant à l'application.
    --

    raise notice 'Calcul des meilleures valeurs d''attributs parmi les doublons dont le NPD est %...', p_npd;

    return query
        select
                    mode() within group (order by i.type) as type,
                    mode() within group (order by i.civilite) as civilite,
                    mode() within group (order by trim(i.nom_patronymique)) as nom_patronymique,
                    mode() within group (order by trim(i.nom_usuel)) as nom_usuel,
                    mode() within group (order by trim(i.prenom1)) as prenom1,
                    mode() within group (order by trim(i.prenom2)) as prenom2,
                    mode() within group (order by trim(i.prenom3)) as prenom3,
                    mode() within group (order by trim(i.email)) as email,
                    mode() within group (order by i.date_naissance) as date_naissance,
                    mode() within group (order by i.nationalite) as nationalite,
                    mode() within group (order by i.supann_id) as supann_id,
                    mode() within group (order by i.etablissement_id) as etablissement_id,
                    mode() within group (order by i.pays_id_nationalite) as pays_id_nationalite
        from individu i
                 join v_individu_doublon v on v.id = i.id and v.npd = p_npd
        where i.histo_destruction is null
        group by v.npd;
end;
$$;


create or replace function substit_create_substituant_individu(data record) returns bigint
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
    insert into individu (id,
                          source_id,
                          source_code,
                          histo_createur_id,
                          type,
                          civilite,
                          nom_patronymique,
                          nom_usuel,
                          prenom1,
                          prenom2,
                          prenom3,
                          email,
                          date_naissance,
                          nationalite,
                          supann_id,
                          etablissement_id,
                          pays_id_nationalite)
    select nextval('individu_id_seq') as id,
           app_source_id() as source_id,
           app_source_source_code() as source_code,
           app_utilisateur_id(),
           data.type,
           data.civilite,
           data.nom_patronymique,
           data.nom_usuel,
           data.prenom1,
           data.prenom2,
           data.prenom3,
           data.email,
           data.date_naissance,
           data.nationalite,
           data.supann_id,
           data.etablissement_id,
           data.pays_id_nationalite
    returning id into substituant_id;

    raise notice '=> Substituant %', substituant_id;

    return substituant_id;
end
$$;


create or replace function substit_update_substituant_individu(p_substituant_id bigint, data record) returns void
    language plpgsql
as
$$begin
    --
    -- Mise à jour des attributs de l'individu substituant spécifié, à partir des valeurs spécifiées.
    --

    update individu
    set histo_modification = current_timestamp,
        histo_modificateur_id = app_utilisateur_id(),
        type = data.type,
        civilite = data.civilite,
        nom_patronymique = data.nom_patronymique,
        nom_usuel = data.nom_usuel,
        prenom1 = data.prenom1,
        prenom2 = data.prenom2,
        prenom3 = data.prenom3,
        email = data.email,
        date_naissance = data.date_naissance,
        nationalite = data.nationalite,
        supann_id = data.supann_id,
        etablissement_id = data.etablissement_id,
        pays_id_nationalite = data.pays_id_nationalite
    where id = p_substituant_id;
end
$$;


--drop function substit_create_all_substitutions_individu
create or replace function substit_create_all_substitutions_individu(limite integer default null) returns smallint
    language plpgsql
as
$$declare
    v_npd varchar(256);
    v_substit_count smallint;
    v_count smallint = 0;
    v_data record;
    v_individu_substituant_id bigint;
    v_individu_substitue record;
begin
    --
    -- Fonction de créations de N substitutions parmi toutes les substitutions possibles.
    --

    -- nombre de nouvelles substitutions possibles
    select count(distinct npd) into v_substit_count from v_individu_doublon v where v.id not in (
        select from_id from individu_substit where histo_destruction is null
    );
    raise notice '>>> Nombre de nouvelles substitutions possibles : %', v_substit_count;

    for v_npd in select distinct npd from v_individu_doublon v where v.id not in (
        select from_id from individu_substit where histo_destruction is null
    ) order by npd loop
            raise notice '>>> Substitution % sur %', v_count+1, v_substit_count;

            v_data = substit_fetch_data_for_substituant_individu(v_npd);
            if v_data is null then
                raise exception 'Anomalie : aucune donnée trouvée pour le NPD % !', v_npd;
            end if;
            v_individu_substituant_id = substit_create_substituant_individu(v_data);
            for v_individu_substitue in select * from v_individu_doublon v where npd = v_npd loop
                    perform substit_add_to_substitution('individu', v_individu_substitue.id, v_npd, v_individu_substituant_id);
                end loop;
            v_count = v_count + 1;

            exit when limite is not null and v_count >= limite;
        end loop;

    raise notice '>> Nombre de substitutions créées : %', v_count;
    return v_count;
end;
$$;