
---------------------------------------------- individu_role_etablissement ----------------------------------------------

--
-- Abandon de INDIVIDU.ETABLISSEMENT_ID,
--            INDIVIDU_COMPL.ETABLISSEMENT_ID,
--            INDIVIDU_COMPL.UNITE_ID
-- au profit de ACTEUR.ETABLISSEMENT_ID
--              ACTEUR.ETABLISSEMENT_FORCE_ID (forçage/remplacement de l'établissement importé avec l'acteur),
--              ACTEUR.UNITE_RECH_ID (saisie manuelle de l'UR de l'acteur, car pas importée),
--              INDIVIDU_ROLE_ETABLISSEMENT.ETABLISSEMENT_ID (périmètre, pour les gest/resp ED/UR par ex).
--
alter table acteur rename column acteur_etablissement_id to etablissement_id;
alter table acteur rename column acteur_uniterech_id to unite_rech_id;
alter table acteur add column etablissement_force_id bigint constraint acteur_etablissement_force_fk references etablissement;
alter table acteur add column refonte_site bool default false not null;

-- individu_compl.etablissement ==> acteur.etablissement_force_id
update acteur a
set etablissement_force_id = ic.etablissement_id,
    histo_modification = current_timestamp,
    histo_modificateur_id = 1,
    refonte_site = true
from individu_compl ic
where a.individu_id = ic.individu_id and
    ic.etablissement_id is not null and
    (a.etablissement_id is null or a.etablissement_id <> ic.etablissement_id);

-- individu_compl.unite_id ==> acteur.unite_rech_id
update acteur a
set unite_rech_id = ic.unite_id,
    histo_modification = current_timestamp,
    histo_modificateur_id = 1,
    refonte_site = true
from individu_compl ic
where a.individu_id = ic.individu_id and
    ic.unite_id is not null and
    (a.unite_rech_id is null or a.unite_rech_id <> ic.unite_id);

-- abandon de individu.etablissement_id (pour l'instant, renommages)
alter table individu rename column etablissement_id to z_etablissement_id;
-- abandon de individu_compl.etablissement_id et unite_id (pour l'instant, renommages)
alter table individu_compl rename column etablissement_id to z_etablissement_id;
alter table individu_compl rename column unite_id to z_unite_id;

--
-- Nouvelle table INDIVIDU_ROLE_PERIMETRE pour ne plus avoir à créer un doublon d'individu pour gérer les sites.
--
create table individu_role_etablissement
(
    id bigserial not null primary key,
    individu_role_id bigint constraint individu_role_etablissement_individu_role_id_fk references individu_role,
    etablissement_id bigint constraint individu_role_etablissement_etablissement_id_fk references etablissement
);
comment on table individu_role_etablissement is 'Ajout de périmètre à l''attribution de rôles aux individus.';
create unique index individu_role_etablissement_uindex on individu_role_etablissement (individu_role_id, etablissement_id);
create index individu_role_etablissement_individu_idx on individu_role_etablissement (individu_role_id);
create index individu_role_etablissement_role_idx on individu_role_etablissement (etablissement_id);
---> NB : la reprise de données pour alimenter individu_role_etablissement devra se faire à la main (cf. vue plus bas) !

--
-- Privilèges
--
insert into CATEGORIE_PRIVILEGE(ID, CODE, LIBELLE, ORDRE)
select nextval('categorie_privilege_id_seq'), 'acteur', 'Acteurs des thèses', 20;

insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (
    select 2000, 'modifier-acteur-de-toutes-theses', 'Modifier les acteurs de n''importe quelle thèse' union
    select 2010, 'modifier-acteur-de-ses-theses', 'Modifier les acteurs des thèses qui me concernent'
)
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d join CATEGORIE_PRIVILEGE cp on cp.CODE = 'acteur';

select privilege__grant_privilege_to_profile('acteur', 'modifier-acteur-de-ses-theses', 'GEST_ED');
select privilege__grant_privilege_to_profile('acteur', 'modifier-acteur-de-toutes-theses', 'BDD');
select privilege__grant_privilege_to_profile('acteur', 'modifier-acteur-de-toutes-theses', 'ADMIN_TECH');


-- drop function substit_fetch_data_for_substituant_individu;
create or replace function substit_fetch_data_for_substituant_individu(p_npd character varying)
    returns TABLE(type character varying, civilite character varying, nom_patronymique character varying, nom_usuel character varying, prenom1 character varying, prenom2 character varying, prenom3 character varying, email character varying, date_naissance timestamp without time zone, nationalite character varying, supann_id character varying, pays_id_nationalite bigint)
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
                    mode() within group (order by i.type) as type,
                    mode() within group (order by i.civilite) as civilite,
                    mode() within group (order by trim(i.nom_patronymique)::varchar) as nom_patronymique,
                    mode() within group (order by trim(i.nom_usuel)::varchar) as nom_usuel,
                    mode() within group (order by trim(i.prenom1)::varchar) as prenom1,
                    mode() within group (order by trim(i.prenom2)::varchar) as prenom2,
                    mode() within group (order by trim(i.prenom3)::varchar) as prenom3,
                    mode() within group (order by trim(i.email)::varchar) as email,
                    mode() within group (order by i.date_naissance) as date_naissance,
                    mode() within group (order by i.nationalite) as nationalite,
                    mode() within group (order by i.supann_id) as supann_id,
                    mode() within group (order by i.pays_id_nationalite) as pays_id_nationalite
        from individu i
                 join v_individu_doublon v on v.id = i.id and v.npd = p_npd
        group by v.npd;
end;
$$;

create or replace function public.substit_create_substituant_individu(data record) returns bigint
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
           data.pays_id_nationalite
    returning id into substituant_id;

    raise notice '=> Substituant %', substituant_id;

    return substituant_id;
end
$$;

create or replace function public.substit_update_substituant_individu(p_substituant_id bigint, data record) returns void
    language plpgsql
as
$$begin
    --
    -- Mise à jour des attributs de l'enregistrement substituant spécifié, à partir des valeurs spécifiées.
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
        pays_id_nationalite = data.pays_id_nationalite
    where id = p_substituant_id;
end
$$;




--
-- Vue utile quand il faudra créer à la main les 'individu_role_etablissement' pour remplacer le bricolage des
-- doublons d'individus : elle liste ceux qui ont, pour le même rôle, 2 établissements différents.

-- => Pour chaque individu, il faudra créer 1 individu_role_etablissement par etablissement distinct,
--    avec l'insert généré par la requête.
--
with tmp as (
    select i.id as individu_id, i.nom_usuel, i.prenom1, ir.id as individu_role_id, r.id as role_id, r.libelle as role_libelle, r.code as role_code, s.sigle,
           coalesce(ic.z_etablissement_id, i.z_etablissement_id) as etablissement_id
    from individu i
             left join individu_compl ic on i.id = ic.individu_id
             join etablissement e on coalesce(ic.z_etablissement_id, i.z_etablissement_id) = e.id
             join individu_role ir on i.id = ir.individu_id
             join role r on ir.role_id = r.id and code in ('RESP_ED', 'RESP_UR')
             join structure s on r.structure_id = s.id
    order by nom_usuel
), doublons as (
    select nom_usuel, prenom1, role_code from tmp group by nom_usuel, prenom1, role_code having count(*) > 1
), req as (select tmp.individu_id,
                  tmp.nom_usuel,
                  tmp.prenom1,
                  tmp.individu_role_id,
                  tmp.role_id,
                  tmp.role_code,
                  tmp.sigle,
                  tmp.etablissement_id,
                  count(*) over (partition by tmp.nom_usuel, tmp.role_code, tmp.etablissement_id) <>
                  count(tmp.etablissement_id) over (partition by tmp.nom_usuel, tmp.role_code) as doublon,
                  'insert into individu_role_etablissement(individu_role_id,etablissement_id) values (' ||
                  individu_role_id || ',' || etablissement_id || ') on conflict do nothing ;'    as sql
           from tmp
                    join doublons d on lower(d.nom_usuel) = lower(tmp.nom_usuel)
           -- where individu_role_id in (14046,11266,1707 ,2966 ,7746 ,9    ,12266,12216,9446 ,8026 ,6    ,8366 ,7    ,1501)
           order by tmp.nom_usuel, role_code, individu_role_id
)
select (select string_agg(to_id::varchar,',') from substit_individu where from_id = req.individu_id) sub, req.* from req
where doublon is true
;

----> NB : ne pas faire les inserts si l'individu est substitué (colonne "sub") !

/*
insert into individu_role_etablissement(individu_role_id,etablissement_id) values (1707,5) on conflict do nothing ;
insert into individu_role_etablissement(individu_role_id,etablissement_id) values (1707,3) on conflict do nothing ;
-- insert into individu_role_etablissement(individu_role_id,etablissement_id) values (2966,5) on conflict do nothing ; -- individu substitué
insert into individu_role_etablissement(individu_role_id,etablissement_id) values (9,5) on conflict do nothing ;
insert into individu_role_etablissement(individu_role_id,etablissement_id) values (7746,3) on conflict do nothing ;
insert into individu_role_etablissement(individu_role_id,etablissement_id) values (12216,5) on conflict do nothing ;
insert into individu_role_etablissement(individu_role_id,etablissement_id) values (12266,3) on conflict do nothing ;
-- insert into individu_role_etablissement(individu_role_id,etablissement_id) values (8026,2) on conflict do nothing ; -- individu substitué
insert into individu_role_etablissement(individu_role_id,etablissement_id) values (9446,3) on conflict do nothing ;
insert into individu_role_etablissement(individu_role_id,etablissement_id) values (9446,21647) on conflict do nothing ;
insert into individu_role_etablissement(individu_role_id,etablissement_id) values (7,5) on conflict do nothing ;
insert into individu_role_etablissement(individu_role_id,etablissement_id) values (1501,3) on conflict do nothing ;
-- insert into individu_role_etablissement(individu_role_id,etablissement_id) values (4966,2) on conflict do nothing ; -- individu substitué
insert into individu_role_etablissement(individu_role_id,etablissement_id) values (12966,2) on conflict do nothing ;
insert into individu_role_etablissement(individu_role_id,etablissement_id) values (12966,16866) on conflict do nothing ;
*/

-- truncate individu_role_etablissement;

select i.nom_usuel, r.*
from individu_role_etablissement ire
         join individu_role ir on ire.individu_role_id = ir.id
         join role r on ir.role_id = r.id
         join individu i on ir.individu_id = i.id
;
