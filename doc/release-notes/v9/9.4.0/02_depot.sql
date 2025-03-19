--Nouveau privilège pour accéder aux informations concernant les corrections attendues sur la fiche d'une thèse
insert into unicaen_privilege_privilege(id, categorie_id, code, libelle, ordre)
with d(categ_code, priv_ordre, priv_code, priv_lib) as (
    select 'these', 3048, 'consultation-correction-autorisee-informations', 'Consulter les informations concernant les correction attendues sur la fiche d''une thèse'
)
select nextval('privilege_id_seq'),
       cp.id,
       d.priv_code,
       d.priv_lib,
       d.priv_ordre
from d
         join unicaen_privilege_categorie cp on cp.code = d.categ_code::text
;

insert into profil_privilege (privilege_id, profil_id)
with data(categ, priv) as (
    select 'these', 'consultation-correction-autorisee-informations'
)
select p.id as privilege_id, profil.id as profil_id
from data
         join profil on profil.role_id in ('ADMIN_TECH', 'DOCTORANT', 'D', 'K', 'RESP_UR', 'GEST_UR', 'GEST_ED', 'BDD')
         join unicaen_privilege_categorie cp on cp.code = data.categ::text
         join unicaen_privilege_privilege p on p.categorie_id = cp.id and p.code = data.priv::text
where not exists(
    select * from profil_privilege where privilege_id = p.id and profil_id = profil.id
    );

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (
    select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id
)
;