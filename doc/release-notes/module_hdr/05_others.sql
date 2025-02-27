INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'these'), 'donner-resultat', 'Attribution du résultat de la thèse saisie dans SyGAL suite à la soutenance', 3060);

--
-- Accord de privilèges à des profils.
--
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'these', 'donner-resultat')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'ADMIN_TECH',
                                           'BDD'
    )
         join unicaen_privilege_categorie cp on cp.CODE = data.categ
         join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id)
;