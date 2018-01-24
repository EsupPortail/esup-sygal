------------------------------------------------------------------------------------------
--- PRIVILEGE
------------------------------------------------------------------------------------------

-- insert the new privilege

insert into PRIVILEGE(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
  select privilege_id_seq.nextval, cp.id, 'version-papier-corrigee', 'Validation de la remise de la version papier corrigée', 3300
  from CATEGORIE_PRIVILEGE cp where cp.CODE = 'these';

-- give the access to the privilege to given role
insert into ROLE_PRIVILEGE(ROLE_ID, PRIVILEGE_ID)
  select r.id, p.id
  from USER_ROLE r, PRIVILEGE p
  where r.ROLE_ID in ('Bureau des doctorats', 'Bibliothèque universitaire', 'Administrateur', 'Administrateur technique')
        and p.CODE in ('version-papier-corrigee');

------------------------------------------------------------------------------------------
--- VALIDATION STEP
------------------------------------------------------------------------------------------

-- add a new validation step
INSERT INTO SODOCT.TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (4, 'VERSION_PAPIER_CORRIGEE', 'Confirmation dépot de la version papier corrigée');

-- creating the view associated to VERSION PAPIER CORRIGEE
CREATE OR REPLACE
  VIEW V_SITU_VERSION_PAPIER_CORRIGEE AS SELECT
    t.id AS these_id,
    v.id as validation_id
  FROM these t
  JOIN VALIDATION v ON v.THESE_ID = t.id
  JOIN TYPE_VALIDATION tv ON tv.ID = v.TYPE_VALIDATION_ID
  WHERE tv.CODE='VERSION_PAPIER_CORRIGEE';

------------------------------------------------------------------------------------------
--- ETAPE DANS LE WORKFLOW
------------------------------------------------------------------------------------------

--add it in WF_ETAPE
INSERT INTO SODOCT.WF_ETAPE (ID, CODE, ORDRE, CHEMIN, OBLIGATOIRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE, DESC_SANS_OBJECTIF) VALUES (60, 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE', 300, 1, 1, 'these/version-papier', 'Remise de l''exemplaire papier de la thèse corrigée', 'Remise de l''exemplaire papier de la thèse corrigée', 'Remise de l''exemplaire papier de la thèse corrigée', null);

--changing the V_WORKFLOW

--
-- REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE  : franchie pas pour le moment
--
select * from (
 WITH tmp_last AS (
     SELECT
       THESE_ID as these_id,
       count(THESE_ID) AS resultat
     FROM V_SITU_VERSION_PAPIER_CORRIGEE
     GROUP BY THESE_ID
 )
 SELECT
   t.id AS                 these_id,
   e.id AS                 etape_id,
   e.code,
   e.ORDRE,
   coalesce(tl.resultat, 0) franchie,
   0,
   1
 FROM these t
   JOIN WF_ETAPE e ON e.code = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'
   LEFT JOIN tmp_last tl ON tl.these_id = t.id
)

-- adding stuff in V_WF_ETAPE_PERTIN

--
-- REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE : étape pertinente si correction attendue
--
SELECT
  t.id AS these_id,
  e.id AS etape_id,
  e.code,
  e.ORDRE
FROM these t
  JOIN WF_ETAPE e ON e.code = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'
WHERE t.CORREC_AUTORISEE is not null

commit;