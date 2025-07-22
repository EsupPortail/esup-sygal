insert into unicaen_privilege_privilege(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (select 100,
                                    'admission-modifier-dossier-admission-gestion',
                                    'Modifier un dossier d''admission même lorsque le circuit de validation est démarré')
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d
         join unicaen_privilege_categorie cp on cp.CODE = 'admission'
WHERE NOT EXISTS (SELECT 1
                  FROM unicaen_privilege_privilege p
                  WHERE p.CODE = d.code);

-- ajout des privilèges au profil ADMIN_TECH et GEST_ED
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'admission', 'admission-modifier-dossier-admission-gestion')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in ('ADMIN_TECH', 'GEST_ED')
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