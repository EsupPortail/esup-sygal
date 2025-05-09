-- Suppression des paramètres inutilisés du module HDR

DELETE FROM unicaen_parametre_parametre
    USING unicaen_parametre_categorie
WHERE unicaen_parametre_parametre.categorie_id = unicaen_parametre_categorie.id
  AND unicaen_parametre_categorie.code = 'SOUTENANCE_HDR' and unicaen_parametre_parametre.code in ('DOC_CONFIDENTIALITE', 'DOC_DELEGATION_SIGNATURE', 'DOC_DELOCALISATION', 'DOC_REDACTION_ANGLAIS');

-- Ajout d'un privilège pour la gestion des documents d'une soutenance
INSERT INTO public.unicaen_privilege_privilege (categorie_id, code, libelle, ordre)
VALUES ((SELECT id FROM unicaen_privilege_categorie WHERE code = 'soutenance'), 'presoutenance-gerer-document', 'Gérer le téléchargement/téléversement d''un document dans la pré-soutenance', 410);

INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'soutenance', 'presoutenance-gerer-document')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
                                           'ADMIN_TECH',
                                           'HDR_GEST',
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