--
-- Base de données SYGAL.
--
-- Schéma : vues.
--

-------------------------- Import ---------------------------

CREATE OR REPLACE VIEW SRC_INDIVIDU AS
  SELECT
    NULL                                     AS id,
    tmp.SOURCE_CODE,
    src.id                                   AS SOURCE_ID,
    TYPE,
    civ                                      AS CIVILITE,
    lib_nom_usu_ind                          AS NOM_USUEL,
    lib_nom_pat_ind                          AS NOM_PATRONYMIQUE,
    lib_pr1_ind                              AS PRENOM1,
    lib_pr2_ind                              AS PRENOM2,
    lib_pr3_ind                              AS PRENOM3,
    EMAIL,
    dat_nai_per                              AS DATE_NAISSANCE,
    lib_nat                                  AS NATIONALITE
  FROM TMP_INDIVIDU tmp
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
;

CREATE OR REPLACE VIEW SRC_DOCTORANT AS
  SELECT
    NULL                                     AS id,
    tmp.SOURCE_CODE,
    src.id                                   AS source_id,
    i.id                                     AS individu_id,
    e.id                                     AS etablissement_id
  FROM TMP_DOCTORANT tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
    JOIN INDIVIDU i ON i.SOURCE_CODE = tmp.INDIVIDU_ID;

CREATE OR REPLACE VIEW SRC_THESE AS
  SELECT
    NULL                                     AS id,
    tmp.SOURCE_CODE,
    src.ID                                   AS source_id,
    e.id                                     AS etablissement_id,
    d.id                                     AS doctorant_id,
    ed.id                                    AS ecole_doct_id,
    ur.id                                    AS unite_rech_id,
    tmp.lib_ths                              AS titre,
    tmp.eta_ths                              AS etat_these,
    to_number(tmp.cod_neg_tre)               AS resultat,
    tmp.lib_int1_dis                         AS lib_disc,
    tmp.dat_deb_ths                          AS date_prem_insc,
    tmp.dat_prev_sou                         AS date_prev_soutenance,
    tmp.dat_sou_ths                          AS date_soutenance,
    tmp.dat_fin_cfd_ths                      AS date_fin_confid,
    tmp.lib_etab_cotut                       AS lib_etab_cotut,
    tmp.lib_pays_cotut                       AS lib_pays_cotut,
    tmp.correction_possible                  AS CORREC_AUTORISEE,
    tem_sou_aut_ths                          AS soutenance_autoris,
    dat_aut_sou_ths                          AS date_autoris_soutenance,
    tem_avenant_cotut                        AS tem_avenant_cotut
  FROM TMP_THESE tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
    JOIN DOCTORANT d ON d.SOURCE_CODE = tmp.DOCTORANT_ID
    LEFT JOIN ECOLE_DOCT ed ON ed.SOURCE_CODE = tmp.ECOLE_DOCT_ID
    LEFT JOIN UNITE_RECH ur ON ur.SOURCE_CODE = tmp.UNITE_RECH_ID;

CREATE OR REPLACE VIEW SRC_ROLE AS
  SELECT
    NULL                                     AS id,
    tmp.SOURCE_CODE,
    src.ID                                   AS source_id,
    e.id                                     AS etablissement_id,
    tmp.LIB_ROJ                              AS libelle,
    to_char(tmp.id)                          AS code,
    tmp.LIB_ROJ||' '||e.CODE                 AS role_id
  FROM TMP_ROLE tmp
    JOIN ETABLISSEMENT e ON e.code = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID;

CREATE OR REPLACE VIEW SRC_ACTEUR AS
  SELECT
    NULL                                     AS id,
    tmp.SOURCE_CODE,
    src.ID                                   AS SOURCE_ID,
    i.id                                     AS INDIVIDU_ID,
    t.id                                     AS THESE_ID,
    r.id                                     AS ROLE_ID,
    tmp.LIB_CPS                              AS QUALITE,
    tmp.LIB_ETB                              AS ETABLISSEMENT,
    tmp.LIB_ROJ_COMPL                        AS LIB_ROLE_COMPL
  FROM TMP_ACTEUR tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
    JOIN INDIVIDU i ON i.SOURCE_CODE = tmp.INDIVIDU_ID
    JOIN THESE t ON t.SOURCE_CODE = tmp.THESE_ID
    JOIN ROLE r ON r.SOURCE_CODE = tmp.ROLE_ID;

CREATE OR REPLACE VIEW SRC_VARIABLE AS
  SELECT
    NULL                                     AS id,
    tmp.SOURCE_CODE,
    src.ID                                   AS SOURCE_ID,
    e.id                                     AS ETABLISSEMENT_ID,
    tmp.COD_VAP                              AS CODE,
    tmp.lib_vap                              AS DESCRIPTION,
    tmp.par_vap                              AS VALEUR,
    tmp.DATE_DEB_VALIDITE,
    tmp.DATE_FIN_VALIDITE
  FROM TMP_VARIABLE tmp
    JOIN ETABLISSEMENT e ON e.CODE = tmp.ETABLISSEMENT_ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID;

---

create or replace view SRC_X_VERIF as
  select 'SRC_INDIVIDU' vue, 'SOURCE_CODE en double' verif, SOURCE_CODE, count(*) nb
  from SRC_INDIVIDU GROUP BY SOURCE_CODE having count(*) > 1
  union
  select 'SRC_DOCTORANT' vue, 'SOURCE_CODE en double' verif, SOURCE_CODE, count(*) nb
  from SRC_DOCTORANT GROUP BY SOURCE_CODE having count(*) > 1
  union
  select 'SRC_THESE' vue, 'SOURCE_CODE en double' verif, SOURCE_CODE, count(*) nb
  from SRC_THESE GROUP BY SOURCE_CODE having count(*) > 1
  union
  select 'SRC_ROLE' vue, 'SOURCE_CODE en double' verif, SOURCE_CODE, count(*) nb
  from SRC_ROLE GROUP BY SOURCE_CODE having count(*) > 1
  union
  select 'SRC_ACTEUR' vue, 'SOURCE_CODE en double' verif, SOURCE_CODE, count(*) nb
  from SRC_ACTEUR GROUP BY SOURCE_CODE having count(*) > 1
  union
  select 'SRC_VARIABLE' vue, 'SOURCE_CODE en double' verif, SOURCE_CODE, count(*) nb
  from SRC_VARIABLE GROUP BY SOURCE_CODE having count(*) > 1;



-------------------------- Workflow ---------------------------

create view V_SITU_ARCHIVAB_VO as
  SELECT
    t.id AS these_id,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VO'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
/

create view V_SITU_ARCHIVAB_VOC as
  SELECT
    t.id AS these_id,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
/

create view V_SITU_ARCHIVAB_VA as
  SELECT
    t.id AS these_id,
    f.RETRAITEMENT,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
/

create view V_SITU_ARCHIVAB_VAC as
  SELECT
    t.id AS these_id,
    f.RETRAITEMENT,
    vf.EST_VALIDE
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
    JOIN VALIDITE_FICHIER vf ON vf.FICHIER_ID = f.id
/

create view V_SITU_RDV_BU_VALIDATION_BU as
  SELECT
    t.id AS these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
  FROM these t
    JOIN VALIDATION v ON v.THESE_ID = t.id and v.HISTO_DESTRUCTEUR_ID is null
    JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'RDV_BU'
/

create view V_SITU_AUTORIS_DIFF_THESE as
  SELECT
    t.id AS these_id,
    d.id AS diffusion_id
  FROM these t
    JOIN DIFFUSION d ON d.THESE_ID = t.id and d.HISTO_DESTRUCTEUR_ID is null
/

create view V_SITU_SIGNALEMENT_THESE as
  SELECT
    t.id AS these_id,
    d.id AS description_id
  FROM these t
    JOIN METADONNEE_THESE d ON d.THESE_ID = t.id
/

create view V_SITU_RDV_BU_SAISIE_DOCT as
  SELECT
    t.id AS these_id,
    CASE WHEN r.COORD_DOCTORANT IS NOT NULL AND r.DISPO_DOCTORANT IS NOT NULL
      THEN 1 ELSE 0 END ok
  FROM these t
    JOIN RDV_BU r ON r.THESE_ID = t.id
/

create view V_SITU_RDV_BU_SAISIE_BU as
  SELECT
    t.id AS these_id,
    CASE WHEN r.VERSION_ARCHIVABLE_FOURNIE = 1 and r.CONVENTION_MEL_SIGNEE = 1 and r.EXEMPL_PAPIER_FOURNI = 1
              and r.PAGE_TITRE_CONFORME = 1 and r.MOTS_CLES_RAMEAU is not null
      THEN 1 ELSE 0 END ok
  FROM these t
    JOIN RDV_BU r ON r.THESE_ID = t.id
/

create view V_SITU_ATTESTATIONS as
  SELECT
    t.id AS these_id,
    a.id AS attestation_id
  FROM these t
    JOIN ATTESTATION a ON a.THESE_ID = t.id and a.HISTO_DESTRUCTEUR_ID is null
/

create view V_SITU_DEPOT_VO as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND RETRAITEMENT IS NULL AND
                      f.HISTO_DESTRUCTION IS NULL
    JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
    JOIN VERSION_FICHIER vf ON f.VERSION_FICHIER_ID = vf.ID AND vf.CODE = 'VO'
/

create view V_SITU_DEPOT_VOC as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND RETRAITEMENT IS NULL AND
                      f.HISTO_DESTRUCTION IS NULL
    JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
    JOIN VERSION_FICHIER vf ON f.VERSION_FICHIER_ID = vf.ID AND vf.CODE = 'VOC'
/

create view V_SITU_DEPOT_VA as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND f.HISTO_DESTRUCTION IS NULL
    JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
/

create view V_SITU_DEPOT_VAC as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 AND f.HISTO_DESTRUCTION IS NULL
    JOIN NATURE_FICHIER nf ON f.NATURE_ID = nf.id AND nf.CODE = 'THESE_PDF'
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
/

create view V_SITU_VERIF_VA as
  SELECT
    t.id AS these_id,
    f.EST_CONFORME
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VA'
/

create view V_SITU_VERIF_VAC as
  SELECT
    t.id AS these_id,
    f.EST_CONFORME
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VAC'
/

create view V_SITU_DEPOT_VC_VALID_DOCT as
  SELECT
    t.id AS these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
  FROM these t
    JOIN VALIDATION v ON v.THESE_ID = t.id and v.HISTO_DESTRUCTEUR_ID is null
    JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'DEPOT_THESE_CORRIGEE'
/

create view V_SITU_DEPOT_VC_VALID_DIR as
  WITH validations_attendues AS (
      SELECT a.THESE_ID, a.INDIVIDU_ID, tv.ID as TYPE_VALIDATION_ID
      FROM ACTEUR a
        JOIN ROLE r on a.ROLE_ID = r.ID and r.CODE = 'D' -- directeur de thèse
        JOIN TYPE_VALIDATION tv on tv.code = 'CORRECTION_THESE'
      where a.HISTO_DESTRUCTION is null
  )
  SELECT
    ROWNUM as id,
    t.id AS these_id,
    va.INDIVIDU_ID,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
  FROM validations_attendues va
    JOIN these t on va.THESE_ID = t.id
    LEFT JOIN VALIDATION v ON v.THESE_ID = t.id and
                              v.INDIVIDU_ID = va.INDIVIDU_ID and -- suppose que l'INDIVIDU_ID soit enregistré lors de la validation
                              v.HISTO_DESTRUCTEUR_ID is null and
                              v.TYPE_VALIDATION_ID = va.TYPE_VALIDATION_ID
/

create view V_SITU_DEPOT_PV_SOUT as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id and f.HISTO_DESTRUCTION is null and f.HISTO_DESTRUCTION is null
    JOIN NATURE_FICHIER nf on f.NATURE_ID = nf.id and nf.CODE = 'PV_SOUTENANCE'
/

create view V_SITU_DEPOT_RAPPORT_SOUT as
  SELECT
    t.id AS these_id,
    f.id AS fichier_id
  FROM these t
    JOIN FICHIER f ON f.THESE_ID = t.id and f.HISTO_DESTRUCTION is null and f.HISTO_DESTRUCTION is null
    JOIN NATURE_FICHIER nf on f.NATURE_ID = nf.id and nf.CODE = 'RAPPORT_SOUTENANCE'
/

create view V_SITU_ATTESTATIONS_VOC as
  SELECT
    t.id AS these_id,
    a.id AS attestation_id
  FROM these t
    JOIN ATTESTATION a ON a.THESE_ID = t.id and a.HISTO_DESTRUCTEUR_ID is null
    -- NB: on se base sur l'existence d'une version corrigée et pas sur t.CORRECTION_AUTORISEE qui peut revenir à null
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
/

create view V_SITU_AUTORIS_DIFF_THESE_VOC as
  SELECT
    t.id AS these_id,
    d.id AS diffusion_id
  FROM these t
    JOIN DIFFUSION d ON d.THESE_ID = t.id and d.HISTO_DESTRUCTEUR_ID is null
    -- NB: on se base sur l'existence d'une version corrigée et pas sur t.CORRECTION_AUTORISEE qui peut revenir à null
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
/

create view V_SITU_VERSION_PAPIER_CORRIGEE as
  SELECT
    t.id AS these_id,
    v.id as validation_id
  FROM these t
    JOIN VALIDATION v ON v.THESE_ID = t.id
    JOIN TYPE_VALIDATION tv ON tv.ID = v.TYPE_VALIDATION_ID
  WHERE tv.CODE='VERSION_PAPIER_CORRIGEE'
/


create view V_WF_ETAPE_PERTIN as
  SELECT
    to_number(these_id) these_id,
    to_number(etape_id) etape_id,
    code,
    ORDRE,
    ROWNUM id
  FROM (
    --
    -- DEPOT_VERSION_ORIGINALE : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE'

    UNION ALL

    --
    -- ATTESTATIONS : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS'

    UNION ALL

    --
    -- AUTORISATION_DIFFUSION_THESE : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'AUTORISATION_DIFFUSION_THESE'

    UNION ALL

    --
    -- SIGNALEMENT_THESE : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'SIGNALEMENT_THESE'

    UNION ALL

    --
    -- ARCHIVABILITE_VERSION_ORIGINALE : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE'

    UNION ALL

    --
    -- DEPOT_VERSION_ARCHIVAGE : étape pertinente si version originale non archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ARCHIVAGE'
      JOIN V_SITU_ARCHIVAB_VO situ ON situ.these_id = t.id AND situ.EST_VALIDE = 0

    UNION ALL

    --
    -- ARCHIVABILITE_VERSION_ARCHIVAGE : étape pertinente si version originale non archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE'
      JOIN V_SITU_ARCHIVAB_VO situ ON situ.these_id = t.id AND situ.EST_VALIDE = 0

    UNION ALL

    --
    -- VERIFICATION_VERSION_ARCHIVAGE : étape pertinente si version d'archivage archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'VERIFICATION_VERSION_ARCHIVAGE'
      JOIN V_SITU_ARCHIVAB_VA situ ON situ.these_id = t.id AND situ.EST_VALIDE = 1

    UNION ALL

    --
    -- RDV_BU_SAISIE_DOCTORANT : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_DOCTORANT'

    UNION ALL

    --
    -- RDV_BU_SAISIE_BU : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_BU'

    UNION ALL

    --
    -- RDV_BU_VALIDATION_BU : étape toujours pertinente
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'RDV_BU_VALIDATION_BU'

    UNION ALL





    --
    -- DEPOT_VERSION_ORIGINALE_CORRIGEE : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- ATTESTATIONS_VERSION_CORRIGEE : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS_VERSION_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- DEPOT_VERSION_ARCHIVAGE_CORRIGEE : étape pertinente si version originale corrigée non archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE'
      JOIN V_SITU_ARCHIVAB_VOC situ ON situ.these_id = t.id AND situ.EST_VALIDE = 0
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE : étape pertinente si version originale corrigée non archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'
      JOIN V_SITU_ARCHIVAB_VOC situ ON situ.these_id = t.id AND situ.EST_VALIDE = 0
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE : étape pertinente si version d'archivage corrigée archivable
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE'
      JOIN V_SITU_ARCHIVAB_VAC situ ON situ.these_id = t.id AND situ.EST_VALIDE = 1
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    --
    -- DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR : étape pertinente si correction attendue
    --
    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR'
    WHERE t.CORREC_AUTORISEE is not null

    UNION ALL

    SELECT
      t.id AS these_id,
      e.id AS etape_id,
      e.code,
      e.ORDRE
    FROM these t
      JOIN WF_ETAPE e ON e.code = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'
    WHERE t.CORREC_AUTORISEE is not null

  )
/

create view V_WORKFLOW as
  SELECT
    ROWNUM id,
    t."THESE_ID",t."ETAPE_ID",t."CODE",t."ORDRE",t."FRANCHIE",t."RESULTAT",t."OBJECTIF"
  FROM (
         --
         -- DEPOT_VERSION_ORIGINALE : franchie si version originale déposée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE'
           LEFT JOIN V_SITU_DEPOT_VO v ON v.these_id = t.id

         UNION ALL

         --
         -- ATTESTATIONS : franchie si données saisies
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.attestation_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.attestation_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS'
           LEFT JOIN V_SITU_ATTESTATIONS v ON v.these_id = t.id

         UNION ALL

         --
         -- AUTORISATION_DIFFUSION_THESE : franchie si données saisies
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.diffusion_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.diffusion_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'AUTORISATION_DIFFUSION_THESE'
           LEFT JOIN V_SITU_AUTORIS_DIFF_THESE v ON v.these_id = t.id

         UNION ALL

         --
         -- SIGNALEMENT_THESE : franchie si données saisies
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.description_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.description_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'SIGNALEMENT_THESE'
           LEFT JOIN V_SITU_SIGNALEMENT_THESE v ON v.these_id = t.id

         UNION ALL

         --
         -- ARCHIVABILITE_VERSION_ORIGINALE : franchie si l'archivabilité de la version originale a été testée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.THESE_ID IS NULL THEN 0 ELSE 1 END franchie,
           --            CASE WHEN v.THESE_ID IS NULL THEN
           --              0 -- test d'archivabilité inexistant
           --            ELSE
           --              CASE WHEN v.EST_VALIDE IS NULL THEN
           --                1 -- test d'archivabilité existant mais résultat indéterminé (plantage)
           --              ELSE
           --                CASE WHEN v.EST_VALIDE = 1 THEN
           --                  1 -- test d'archivabilité réussi
           --                ELSE
           --                  0 -- test d'archivabilité échoué
           --                END
           --              END
           --            END franchie,
           CASE WHEN v.EST_VALIDE IS NULL OR v.EST_VALIDE = 0 THEN 0 ELSE 1 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE'
           LEFT JOIN V_SITU_ARCHIVAB_VO v ON v.these_id = t.id

         UNION ALL

         --
         -- DEPOT_VERSION_ARCHIVAGE : franchie si version d'archivage déposée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ARCHIVAGE'
           LEFT JOIN V_SITU_DEPOT_VA v ON v.these_id = t.id
           LEFT JOIN fichier f ON f.id = v.fichier_id

         UNION ALL

         --
         -- ARCHIVABILITE_VERSION_ARCHIVAGE : franchie si l'archivabilité de la version d'archivage a été testée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.EST_VALIDE IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.EST_VALIDE IS NULL OR v.EST_VALIDE = 0
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE'
           LEFT JOIN V_SITU_ARCHIVAB_VA v ON v.these_id = t.id

         UNION ALL

         --
         -- VERIFICATION_VERSION_ARCHIVAGE : franchie si vérification de la version originale effectuée (peu importe la réponse)
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.EST_CONFORME IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.EST_CONFORME IS NULL OR v.EST_CONFORME = 0
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'VERIFICATION_VERSION_ARCHIVAGE'
           LEFT JOIN V_SITU_VERIF_VA v ON v.these_id = t.id

         UNION ALL

         --
         -- RDV_BU_SAISIE_DOCTORANT : franchie si données doctorant saisies
         --
         SELECT
           t.id AS                      these_id,
           e.id AS                      etape_id,
           e.code,
           e.ORDRE,
           coalesce(v.ok, 0)            franchie,
           (CASE WHEN rdv.COORD_DOCTORANT IS NULL
             THEN 0
            ELSE 1 END +
            CASE WHEN rdv.DISPO_DOCTORANT IS NULL
              THEN 0
            ELSE 1 END)                 resultat,
           2                            objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_DOCTORANT'
           LEFT JOIN V_SITU_RDV_BU_SAISIE_DOCT v ON v.these_id = t.id
           LEFT JOIN RDV_BU rdv ON rdv.THESE_ID = t.id

         UNION ALL

         --          --
         --          -- RDV_BU_SAISIE_BU : franchie si données BU saisies
         --          --
         --          SELECT
         --            t.id AS                                                                          these_id,
         --            e.id AS                                                                          etape_id,
         --            e.code,
         --            e.ORDRE,
         --            coalesce(v.ok, 0)                                                                franchie,
         --            CASE WHEN rdv.MOTS_CLES_RAMEAU IS NULL THEN 0 ELSE 1 END +
         --            coalesce(rdv.VERSION_ARCHIVABLE_FOURNIE, 0) +
         --            coalesce(rdv.EXEMPL_PAPIER_FOURNI, 0) +
         --            coalesce(rdv.CONVENTION_MEL_SIGNEE, 0)                                           resultat,
         --            4                                                                                objectif
         --          FROM these t
         --            JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_BU'
         --            LEFT JOIN V_SITU_RDV_BU_SAISIE_BU v ON v.these_id = t.id
         --            LEFT JOIN RDV_BU rdv ON rdv.THESE_ID = t.id
         --
         --       UNION ALL

         --
         -- RDV_BU_VALIDATION_BU : franchie si données BU saisies ET une validation BU existe
         --
         SELECT
           t.id AS               these_id,
           e.id AS               etape_id,
           e.code,
           e.ORDRE,
           coalesce(vs.ok, 0) * coalesce(v.valide, 0) franchie,
           coalesce(vs.ok, 0) + coalesce(v.valide, 0) resultat,
           2 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'RDV_BU_VALIDATION_BU'
           LEFT JOIN V_SITU_RDV_BU_SAISIE_BU vs ON vs.these_id = t.id
           LEFT JOIN V_SITU_RDV_BU_VALIDATION_BU v ON v.these_id = t.id

         UNION ALL




         --
         -- DEPOT_VERSION_ORIGINALE_CORRIGEE : franchie si version originale corrigée déposée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ORIGINALE_CORRIGEE'
           LEFT JOIN V_SITU_DEPOT_VOC v ON v.these_id = t.id

         UNION ALL

         --
         -- ATTESTATIONS_VERSION_CORRIGEE : franchie si données saisies
         --
         SELECT
           t.id AS these_id,
           e.id AS etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.attestation_id IS NULL THEN 0 ELSE 1 END franchie,
           CASE WHEN v.attestation_id IS NULL THEN 0 ELSE 1 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS_VERSION_CORRIGEE'
           LEFT JOIN V_SITU_ATTESTATIONS_VOC v ON v.these_id = t.id

         UNION ALL

         --
         -- AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE : franchie si données saisies
         --
         SELECT
           t.id AS these_id,
           e.id AS etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.diffusion_id IS NULL THEN 0 ELSE 1 END franchie,
           CASE WHEN v.diffusion_id IS NULL THEN 0 ELSE 1 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE'
           LEFT JOIN V_SITU_AUTORIS_DIFF_THESE_VOC v ON v.these_id = t.id

         UNION ALL

         --
         -- ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE : franchie si l'archivabilité de la version originale corrigée a été testée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.THESE_ID IS NULL THEN 0 ELSE 1 END franchie,
           CASE WHEN v.EST_VALIDE IS NULL OR v.EST_VALIDE = 0 THEN 0 ELSE 1 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'
           LEFT JOIN V_SITU_ARCHIVAB_VOC v ON v.these_id = t.id

         UNION ALL

         --
         -- DEPOT_VERSION_ARCHIVAGE_CORRIGEE : franchie si version d'archivage corrigée déposée
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END franchie,
           CASE WHEN v.fichier_id IS NULL
             THEN 0
           ELSE 1 END resultat,
           1          objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE'
           LEFT JOIN V_SITU_DEPOT_VAC v ON v.these_id = t.id
           LEFT JOIN fichier f ON f.id = v.fichier_id

         UNION ALL

         --
         -- ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE : franchie si la version d'archivage corrigée est archivable
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.EST_VALIDE = 1 THEN 1 ELSE 0 END franchie,
           CASE WHEN v.EST_VALIDE = 1 THEN 1 ELSE 0 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'
           LEFT JOIN V_SITU_ARCHIVAB_VAC v ON v.these_id = t.id

         UNION ALL

         --
         -- VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE : franchie si la version corrigée est certifiée conforme
         --
         SELECT
           t.id AS    these_id,
           e.id AS    etape_id,
           e.code,
           e.ORDRE,
           CASE WHEN v.EST_CONFORME = 1 THEN 1 ELSE 0 END franchie,
           CASE WHEN v.EST_CONFORME = 1 THEN 1 ELSE 0 END resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE'
           LEFT JOIN V_SITU_VERIF_VAC v ON v.these_id = t.id

         UNION ALL

         --
         -- DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT : franchie si la validation attendue existe
         --
         SELECT
           t.id AS               these_id,
           e.id AS               etape_id,
           e.code,
           e.ORDRE,
           coalesce(v.valide, 0) franchie,
           coalesce(v.valide, 0) resultat,
           1 objectif
         FROM these t
           JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'
           LEFT JOIN V_SITU_DEPOT_VC_VALID_DOCT v ON v.these_id = t.id

         UNION ALL

         --
         -- DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR : franchie si toutes les validations attendues existent
         --
         select * from (
           WITH tmp AS (
               SELECT
                 these_id,
                 sum(valide)   AS resultat,
                 count(valide) AS objectif
               FROM V_SITU_DEPOT_VC_VALID_DIR
               GROUP BY these_id
           )
           SELECT
             t.id AS                 these_id,
             e.id AS                 etape_id,
             e.code,
             e.ORDRE,
             coalesce(v.resultat, 0) franchie,
             coalesce(v.resultat, 0) resultat,
             v.objectif
           FROM these t
             JOIN WF_ETAPE e ON e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR'
             LEFT JOIN tmp v ON v.these_id = t.id
         )

         UNION ALL
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
         --          e.code,
         --          e.ORDRE,
         --          0 franchie,
         --          0 resultat,
         --          1 objectif
         --        FROM V_SITU_VERSION_PAPIER_CORRIGEE
         --          JOIN WF_ETAPE e ON e.code = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'



         -- LEFT JOIN V_SITU_DEPOT_VO v ON v.these_id = t.id



       ) t
    JOIN V_WF_ETAPE_PERTIN v ON t.these_id = v.these_id AND t.etape_id = v.etape_id
/


---------------------------- MV ------------------------------

CREATE MATERIALIZED VIEW MV_RECHERCHE_THESE AS
  with acteurs as (
    select a.these_id, i.nom_usuel, INDIVIDU_ID
    from individu i
      join acteur a on i.id = a.individu_id
      join these t on t.id = a.these_id
      join role r on a.role_id = r.id and r.CODE in ('D') -- directeur de thèse
  )
  select
    t.source_code code_these,
    d.source_code code_doctorant,
    ed.source_code code_ecole_doct,
    trim(UNICAEN_ORACLE.str_reduce(
       t.COD_UNIT_RECH || ' ' || t.TITRE || ' ' ||
       d.SOURCE_CODE || ' ' || id.NOM_PATRONYMIQUE || ' ' || id.NOM_USUEL || ' ' || id.PRENOM1 || ' ' ||
       a.nom_usuel)) as haystack
  from these t
    join doctorant d on d.id = t.doctorant_id
    join individu id on id.id = d.INDIVIDU_ID
    join these th on th.source_code = t.source_code
    --join mv_thesard mvd on mvd.source_code = d.source_code
    left join ecole_doct ed on t.ecole_doct_id = ed.id
    left join acteurs a on a.these_id = t.id
    left join individu ia on ia.id = a.INDIVIDU_ID
;