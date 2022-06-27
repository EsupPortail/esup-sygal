--
-- Individu
--

alter table tmp_individu add cod_pay_nat varchar(3);
alter table individu add pays_id_nationalite bigint references pays(id);

drop view src_individu cascade
;
create or replace view src_individu (id, source_code, source_id, type, supann_id, civilite, nom_usuel, nom_patronymique,
                                     prenom1, prenom2, prenom3, email, date_naissance, nationalite)
as
SELECT NULL::text          AS id,
       tmp.source_code,
       src.id              AS source_id,
       tmp.type,
       tmp.supann_id,
       tmp.civ             AS civilite,
       tmp.lib_nom_usu_ind AS nom_usuel,
       tmp.lib_nom_pat_ind AS nom_patronymique,
       tmp.lib_pr1_ind     AS prenom1,
       tmp.lib_pr2_ind     AS prenom2,
       tmp.lib_pr3_ind     AS prenom3,
       tmp.email,
       tmp.dat_nai_per     AS date_naissance,
       tmp.lib_nat         AS nationalite,
       p.id                as pays_id_nationalite
FROM tmp_individu tmp
    JOIN source src ON src.id = tmp.source_id
    left join pays p on p.code_pays_apogee = tmp.cod_pay_nat;


insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (
    select 1, 'lister', 'Lister les individus' union
    select 2, 'consulter', 'Consulter la fiche détaillée d''un individu' union
    select 3, 'ajouter', 'Créer un individu' union
    select 4, 'modifier', 'Modifier un individu' union
    select 5, 'supprimer', 'Supprimer un individu'
)
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre from d
join CATEGORIE_PRIVILEGE cp on cp.CODE = 'individu'
;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'individu', 'lister' union all
    select 'individu', 'consulter' union all
    select 'individu', 'ajouter' union all
    select 'individu', 'modifier' union all
    select 'individu', 'supprimer'
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID from data
join PROFIL on profil.ROLE_ID in ('ADMIN_TECH', 'BDD')
join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id) ;

insert into PROFIL_TO_ROLE (PROFIL_ID, ROLE_ID)
with data(PROFIL_CODE, ROLE_ROLE_ID) as (
    select 'ADMIN_TECH', 'Administrateur technique' union
    select 'BDD', 'Maison du doctorat UCN' union
    select 'BDD', 'Maison du doctorat URN' union
    select 'BDD', 'Maison du doctorat ULHN' union
    select 'BDD', 'Maison du doctorat INSA'
)
select pr.id, r.id
from data
         join PROFIL pr on pr.ROLE_ID = data.PROFIL_CODE
         join role r on r.ROLE_ID = data.ROLE_ROLE_ID
where not exists (
        select * from PROFIL_TO_ROLE where PROFIL_ID = pr.id and ROLE_ID = r.id
    ) ;

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (
        select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id
    )
;