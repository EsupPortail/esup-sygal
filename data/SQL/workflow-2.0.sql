
-- nouvelles étapes 'version corrigée'
insert into WF_ETAPE(ID, CODE, ORDRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE)
  select
    WF_ETAPE_ID_SEQ.nextval,
    'ATTESTATIONS_VERSION_CORRIGEE',
    210,
    'these/depot-version-corrigee',
    'Attestations version corrigée',
    'Attestations version corrigée',
    'Attestations version corrigée non renseignées'
  from dual;
insert into WF_ETAPE(ID, CODE, ORDRE, ROUTE, LIBELLE_ACTEUR, LIBELLE_AUTRES, DESC_NON_FRANCHIE)
  select
    WF_ETAPE_ID_SEQ.nextval,
    'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE',
    220,
    'these/depot-version-corrigee',
    'Autorisation de diffusion de la version corrigée',
    'Autorisation de diffusion de la version corrigée',
    'Autorisation de diffusion de la version corrigée non remplie'
  from dual;

-- modif ordre des étapes 'version corrigée' existantes
update wf_etape set ordre = 200 where code = 'DEPOT_VERSION_ORIGINALE_CORRIGEE';
update wf_etape set ordre = 240 where code = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE';
update wf_etape set ordre = 250 where code = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE';
update wf_etape set ordre = 260 where code = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE';
update wf_etape set ordre = 270 where code = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE';
update wf_etape set ordre = 280 where code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT';
update wf_etape set ordre = 290 where code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR';

-- modif routes d'étapes
update wf_etape set route = 'these/depot-version-corrigee'     where code = 'DEPOT_VERSION_ORIGINALE_CORRIGEE';
update wf_etape set route = 'these/archivage-version-corrigee' where code = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE';
update wf_etape set route = 'these/archivage-version-corrigee' where code = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE';
update wf_etape set route = 'these/archivage-version-corrigee' where code = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE';
update wf_etape set route = 'these/archivage-version-corrigee' where code = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE';

-- modif chemin
update wf_etape set CHEMIN = 2 where code = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE';
update wf_etape set CHEMIN = 2 where code = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE';
update wf_etape set CHEMIN = 2 where code = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE';



select * from wf_etape order by ordre;

--rollback;
--commit;



/**
 * Vues de situation
 * -----------------
 * Elles fournissent les données nécessaires au calcul de l'état de chaque étape.
 */

CREATE OR REPLACE VIEW V_SITU_ATTESTATIONS_VOC AS
  SELECT
    t.id AS these_id,
    a.id AS attestation_id
  FROM these t
    JOIN ATTESTATION a ON a.THESE_ID = t.id and a.HISTO_DESTRUCTEUR_ID is null
    -- NB: on se base sur l'existence d'une version corrigée et pas sur t.CORRECTION_AUTORISEE qui peut revenir à null
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC';

CREATE OR REPLACE VIEW V_SITU_AUTORIS_DIFF_THESE_VOC AS
  SELECT
    t.id AS these_id,
    d.id AS diffusion_id
  FROM these t
    JOIN DIFFUSION d ON d.THESE_ID = t.id and d.HISTO_DESTRUCTEUR_ID is null
    -- NB: on se base sur l'existence d'une version corrigée et pas sur t.CORRECTION_AUTORISEE qui peut revenir à null
    JOIN FICHIER f ON f.THESE_ID = t.id AND EST_ANNEXE = 0 AND EST_EXPURGE = 0 and f.HISTO_DESTRUCTION is null
    JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC';







select * from V_WF_ETAPE_PERTIN;


/**
 * Pertinence des étapes
 * ---------------------
 * Vue fournissant les étapes pertinentes pour chaque thèse.
 */

CREATE OR REPLACE VIEW V_WF_ETAPE_PERTIN AS
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

  );






select * from V_WORKFLOW;



CREATE OR REPLACE VIEW V_WORKFLOW AS
  SELECT
    ROWNUM id,
    t.*
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

    ) t
    JOIN V_WF_ETAPE_PERTIN v ON t.these_id = v.these_id AND t.etape_id = v.etape_id;




/*
update these set CORREC_AUTORISEE = 'mineure' where id = 29085;
commit;

update these set CORREC_AUTORISEE = null where id = 29085;
commit;



select
  id,
  these_id,
  etape_id,
  code,
  ORDRE,
  franchie,
  resultat,
  objectif,
  SODOCT_WORKFLOW.ATTEIGNABLE(etape_id, these_id) atteignable
from V_WORKFLOW
where these_id = 29085
;
*/