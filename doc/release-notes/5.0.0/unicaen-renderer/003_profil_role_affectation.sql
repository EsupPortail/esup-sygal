-- AFFECTATION DES PRIVILÈGES AUX PROFILS ADMIN TECH -------------------------------------------------------------------

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'documentmacro'::text, 'documentmacro_index'::text union
    select 'documentmacro'::text, 'documentmacro_ajouter'::text union
    select 'documentmacro'::text, 'documentmacro_modifier'::text union
    select 'documentmacro'::text, 'documentmacro_supprimer'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
join PROFIL on profil.ROLE_ID in ('ADMIN_TECH')
join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
    select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'documenttemplate'::text, 'documenttemplate_index'::text union
    select 'documenttemplate'::text, 'documenttemplate_modifier'::text union
    select 'documenttemplate'::text, 'documenttemplate_supprimer'::text union
    select 'documenttemplate'::text, 'documenttemplate_ajouter'::text union
    select 'documenttemplate'::text, 'documenttemplate_afficher'::text
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('ADMIN_TECH')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
    ) ;

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (
    select 'documentcontenu'::text, 'documentcontenu_index'::text union
    select 'documentcontenu'::text, 'documentcontenu_afficher'::text union
    select 'documentcontenu'::text, 'documentcontenu_supprimer'
)
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('ADMIN_TECH')
         join CATEGORIE_PRIVILEGE cp on cp.CODE = data.categ
         join PRIVILEGE p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (
        select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id
);

-- REAPPLICATION AUX ROLES ---------------------------------------------------------------------------------------------

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (
        select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id
    )
;







