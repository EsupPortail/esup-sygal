--
-- Table des logs
--
create table step_star_log
(
    id bigserial constraint stepstar_log_pk primary key,
    these_id bigint constraint stepstar_log_these_id_fk references these on delete cascade,
    started_on timestamp not null,
    ended_on timestamp not null,
    success boolean not null,
    operation varchar(64) not null,
    log text not null,
    command varchar(256) not null,
    tef_file_content_hash varchar(64),
    tef_file_content text,
    has_problems boolean default false not null,
    tag varchar(64)
);
-- NB : `step_star_log_id_seq` créée automatiquement.

--
-- Nouvelle catégorie.
--
insert into CATEGORIE_PRIVILEGE(ID, CODE, LIBELLE, ORDRE)
select nextval('categorie_privilege_id_seq'), 'step-star', 'STEP-STAR', 20;

--
-- Nouveaux privilèges.
--
insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (
    select 10, 'log-lister', 'Lister les logs' union all
    select 20, 'log-consulter', 'Consulter les détails d''un log' union all
    select 30, 'tef-telecharger', 'Télécharger le fichier TEF envoyé'
)
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d join CATEGORIE_PRIVILEGE cp on cp.CODE = 'step-star'
;

--
-- Accord de privilèges à des profils.
--
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'step-star', 'log-lister' union all
    select 'step-star', 'log-consulter' union all
    select 'step-star', 'tef-telecharger'
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('ADMIN_TECH', 'BU')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists(
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    );

--
-- Attribution automatique des privilèges aux rôles, d'après ce qui est spécifié dans :
--   - PROFIL_TO_ROLE (profils appliqués à chaque rôle) et
--   - PROFIL_PRIVILEGE (privilèges accordés à chaque profil).
--
insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (
        select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id
    )
;
