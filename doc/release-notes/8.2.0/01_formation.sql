-- Module Formation

--Ajout d'un paramètre pour spécifier le délai pour une annulation d'inscription
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur,
                                                ordre)
VALUES (20, 2, 'DELAI_ANNULATION_INSCRIPTION', 'Delai annulation', null, 'Number', '21', 20) ON CONFLICT DO NOTHING;


--Ajout d'un privilège pour voir le lieu des séances d'une session
INSERT INTO privilege(id, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (SELECT 'voir_lieu', 'Voir le lieu de la session', 8)
SELECT nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
FROM d
         JOIN categorie_privilege cp ON cp.CODE = 'formation_session'
WHERE NOT EXISTS (SELECT 1
                  FROM PRIVILEGE p
                  WHERE p.CODE = d.code);
--
-- Accord du privilège aux profils ADMIN_TECH, MAISON DU DOCTORAT, FORMATEUR, RESPONSABLE_ED, DOCTORANT.
--
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'formation_session', 'voir_lieu')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'ADMIN_TECH',
                                           'BDD',
                                           'FORMATEUR',
                                           'RESP_ED',
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

--
-- Accord de privilèges au profil Formateur pour accéder à l'index de ses formations.
--
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'formation', 'index_formateur')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
    'FORMATEUR'
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