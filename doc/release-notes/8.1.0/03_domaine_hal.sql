--
-- DomaineHal
--
-- Version 8.1.0
--

--
-- Ajout des informations concernant l'import des domaines HAL
--
INSERT INTO source(id, code, libelle, etablissement_id, importable)
VALUES (nextval('source_id_seq'), 'HAL', 'HAL', null, true) ;

--Suppression des possibles données existantes concernant l'import des domaines HAL
drop view if exists v_diff_domaine_hal;
drop view if exists src_domaine_hal;
drop table if exists tmp_domaine_hal;
drop table if exists domaine_hal;

--Création de la table TEMP de Domaine HAL
create table if not exists public.tmp_domaine_hal
(
    id                    bigserial,
    docid                 bigint,
    haveNext_bool         boolean,
    code_s                varchar(64),
    fr_domain_s           varchar,
    en_domain_s           varchar,
    level_i               bigint,
    parent_id             bigint,
    insert_date           timestamp(0) default ('now'::text)::timestamp without time zone,
    source_id             bigint                                                             not null,
    source_code           varchar(64)                                                        not null,
    source_insert_date    timestamp    default ('now'::text)::timestamp without time zone,
    histo_creation        timestamp(0) default ('now'::text)::timestamp(0) without time zone not null,
    histo_modification    timestamp(0),
    histo_destruction     timestamp(0),
    histo_createur_id     bigint                                                             not null,
    histo_modificateur_id bigint,
    histo_destructeur_id  bigint
    );

create index tmp_domaine_hal_source_code_index
    on public.tmp_domaine_hal (source_code);

create index tmp_domaine_hal_source_id_index
    on public.tmp_domaine_hal (source_id);

create unique index tmp_domaine_hal_unique_index
    on public.tmp_domaine_hal (source_id, source_code);

--Création de la table Domaine HAL
create table if not exists public.domaine_hal
(
    id                    bigserial                                                    not null
    primary key,
    docid                 bigint,
    haveNext_bool         boolean,
    code_s                varchar(64),
    fr_domain_s           varchar,
    en_domain_s           varchar,
    level_i               bigint,
    parent_id             bigint REFERENCES domaine_hal (id),
    histo_creation        timestamp default ('now'::text)::timestamp without time zone not null,
    histo_createur_id     bigint                                                       not null
    constraint domaine_hal_hcfk
    references public.utilisateur
                                                                     on delete cascade,
    histo_modification    timestamp,
    histo_modificateur_id bigint
    constraint domaine_hal_hmfk
    references public.utilisateur
                                                                     on delete cascade,
    histo_destruction     timestamp,
    histo_destructeur_id  bigint
    constraint domaine_hal_hdfk
    references public.utilisateur
                                                                     on delete cascade,
    source_id             bigint                                                       not null
    constraint domaine_hal_source_fk
    references public.source
                                                                     on delete cascade,
    source_code           varchar(64)                                                  not null
    );

create index domaine_hal_hc_idx
    on public.domaine_hal (histo_createur_id);

create index domaine_hal_hd_idx
    on public.domaine_hal (histo_destructeur_id);

create index domaine_hal_hm_idx
    on public.domaine_hal (histo_modificateur_id);

create unique index domaine_hal_source_code_un
    on public.domaine_hal (source_code);

create index domaine_hal_source_idx
    on public.domaine_hal (source_id);


--Création de la vue src_domaine_hal avec les infos de tmp_domaine_hal
create or replace view src_domaine_hal
            (id, source_code, source_id, docid, haveNext_bool, code_s, fr_domain_s, en_domain_s, level_i, parent_id) as
select NULL::bigint                                                 AS id,
        tmp.source_code,
       src.id                                                       AS source_id,
       tmp.docid,
       tmp.haveNext_bool,
       tmp.code_s,
       tmp.fr_domain_s,
       tmp.en_domain_s,
       tmp.level_i,
       (SELECT id FROM tmp_domaine_hal WHERE docid = tmp.parent_id) AS parent_id
FROM tmp_domaine_hal tmp
         JOIN source src ON src.id = tmp.source_id
;

CREATE TABLE IF NOT EXISTS public.domaine_hal_these
(
    these_id   bigint NOT NULL,
    domaine_id bigint NOT NULL
);

--
-- Ajout des privilèges liés à la gestion des domaines HAL
--
insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (
    select 3100, 'modification-domaines-hal-these', 'Modifier les domaines HAL de sa thèse'
)
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d join CATEGORIE_PRIVILEGE cp on cp.CODE = 'these';

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'these', 'modification-domaines-hal-these')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'ADMIN_TECH',
                                           'BDD',
                                           'GEST_ED',
                                           'DOCTORANT'

    )
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id)
;