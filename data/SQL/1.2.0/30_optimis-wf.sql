--
--
-- AMÉLIORATION DU "MOTEUR" DE WORKFLOW.
--
--

--
-- Amélioration de certaines vues V_SITU_* : supression de jointures inutiles.
--
create or replace view V_SITU_AUTORIS_DIFF_THESE as
SELECT
    d.these_id,
    d.id AS diffusion_id
FROM DIFFUSION d
where d.HISTO_DESTRUCTEUR_ID is null
/
create or replace view V_SITU_RDV_BU_VALIDATION_BU as
SELECT
    v.these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
FROM VALIDATION v
         JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'RDV_BU'
where v.HISTO_DESTRUCTEUR_ID is null
/

create or replace view V_SITU_RDV_BU_SAISIE_DOCT as
SELECT
    r.these_id,
    CASE WHEN r.COORD_DOCTORANT IS NOT NULL AND r.DISPO_DOCTORANT IS NOT NULL
             THEN 1 ELSE 0 END ok
FROM RDV_BU r
/

create or replace view V_SITU_RDV_BU_SAISIE_BU as
SELECT
    r.these_id,
    CASE WHEN r.VERSION_ARCHIVABLE_FOURNIE = 1 and r.CONVENTION_MEL_SIGNEE = 1 and r.EXEMPL_PAPIER_FOURNI = 1
        and r.MOTS_CLES_RAMEAU is not null
             THEN 1 ELSE 0 END ok
FROM RDV_BU r
/

create or replace view V_SITU_SIGNALEMENT_THESE as
SELECT
    d.these_id,
    d.id AS description_id
FROM METADONNEE_THESE d
/

create or replace view V_SITU_ATTESTATIONS as
SELECT
    a.these_id,
    a.id AS attestation_id
FROM ATTESTATION a
where a.HISTO_DESTRUCTEUR_ID is null
/

create or replace view V_SITU_DEPOT_VC_VALID_DOCT as
SELECT
    v.these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
FROM VALIDATION v
         JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'DEPOT_THESE_CORRIGEE'
where v.HISTO_DESTRUCTEUR_ID is null
/

create or replace view V_SITU_DEPOT_VC_VALID_DIR as
    WITH validations_attendues AS (
        SELECT a.THESE_ID, a.INDIVIDU_ID, tv.ID as TYPE_VALIDATION_ID
        FROM ACTEUR a
                 JOIN ROLE r on a.ROLE_ID = r.ID and r.CODE = 'D' -- directeur de thèse
                 JOIN TYPE_VALIDATION tv on tv.code = 'CORRECTION_THESE'
        where a.HISTO_DESTRUCTION is null
    )
    SELECT
        ROWNUM as id,
        va.these_id,
        va.INDIVIDU_ID,
        CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
    FROM validations_attendues va
--              JOIN these t on va.THESE_ID = t.id
             LEFT JOIN VALIDATION v ON --v.THESE_ID = t.id and
                v.INDIVIDU_ID = va.INDIVIDU_ID and -- suppose que l'INDIVIDU_ID soit enregistré lors de la validation
                v.HISTO_DESTRUCTEUR_ID is null and
                v.TYPE_VALIDATION_ID = va.TYPE_VALIDATION_ID
/

create or replace view V_SITU_VERSION_PAPIER_CORRIGEE as
SELECT
    v.these_id,
    v.id as validation_id
FROM VALIDATION v
         JOIN TYPE_VALIDATION tv ON tv.ID = v.TYPE_VALIDATION_ID
WHERE tv.CODE='VERSION_PAPIER_CORRIGEE'
/

create or replace view V_SITU_VALIDATION_PAGE_COUV as
SELECT
    v.these_id,
    CASE WHEN v.id is not null THEN 1 ELSE 0 END valide
FROM VALIDATION v
         JOIN TYPE_VALIDATION tv on v.TYPE_VALIDATION_ID = tv.id and tv.code = 'PAGE_DE_COUVERTURE'
where v.HISTO_DESTRUCTEUR_ID is null
/


--
-- Amélioration V_WORKFLOW : il devient inutile d'utiliser APP_WORKFLOW.ATTEIGNABLE() :-)
--
create or replace view V_WORKFLOW as
    SELECT
        ROWNUM as id,
        t.THESE_ID,
        t.ETAPE_ID,
        t.CODE,
        t.ORDRE,
        t.FRANCHIE,
        t.RESULTAT,
        t.OBJECTIF,
        -- NB: dans les 3 lignes suivantes, c'est la même expression 'dense_rank() over(...)' qui est répétée :
        (dense_rank() over(partition by t.THESE_ID, t.FRANCHIE order by t.ORDRE)) dense_rank,
        case when t.FRANCHIE = 1 or (dense_rank() over(partition by t.THESE_ID, t.FRANCHIE order by t.ORDRE)) = 1 then 1 else 0 end atteignable,
        case when (dense_rank() over(partition by t.THESE_ID, t.FRANCHIE order by t.ORDRE)) = 1 and t.FRANCHIE = 0 then 1 else 0 end courante
    FROM (

         --
         -- VALIDATION_PAGE_DE_COUVERTURE : franchie si version page de couverture validée
         --
         SELECT
             t.id AS    these_id,
             e.id AS    etape_id,
             e.code,
             e.ORDRE,
             CASE WHEN v.valide IS NULL THEN 0 ELSE 1 END franchie,
             CASE WHEN v.valide IS NULL THEN 0 ELSE 1 END resultat,
             1 objectif
         FROM these t
                  JOIN WF_ETAPE e ON e.code = 'VALIDATION_PAGE_DE_COUVERTURE'
                  LEFT JOIN V_SITU_VALIDATION_PAGE_COUV v ON v.these_id = t.id

         UNION ALL

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
             -- CASE WHEN v.THESE_ID IS NULL THEN
             --   0 -- test d'archivabilité inexistant
             -- ELSE
             --   CASE WHEN v.EST_VALIDE IS NULL THEN
             --     1 -- test d'archivabilité existant mais résultat indéterminé (plantage)
             --   ELSE
             --     CASE WHEN v.EST_VALIDE = 1 THEN
             --       1 -- test d'archivabilité réussi
             --     ELSE
             --       0 -- test d'archivabilité échoué
             --     END
             --   END
             -- END franchie,
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

         --    --
         --    -- RDV_BU_SAISIE_BU : franchie si données BU saisies
         --    --
         --    SELECT
         --      t.id AS                                                                          these_id,
         --      e.id AS                                                                          etape_id,
         --      e.code,
         --      e.ORDRE,
         --      coalesce(v.ok, 0)                                                                franchie,
         --      CASE WHEN rdv.MOTS_CLES_RAMEAU IS NULL THEN 0 ELSE 1 END +
         --      coalesce(rdv.VERSION_ARCHIVABLE_FOURNIE, 0) +
         --      coalesce(rdv.EXEMPL_PAPIER_FOURNI, 0) +
         --      coalesce(rdv.CONVENTION_MEL_SIGNEE, 0)                                           resultat,
         --      4                                                                                objectif
         --    FROM these t
         --      JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_BU'
         --      LEFT JOIN V_SITU_RDV_BU_SAISIE_BU v ON v.these_id = t.id
         --      LEFT JOIN RDV_BU rdv ON rdv.THESE_ID = t.id
         --
         -- UNION ALL

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
                   case when coalesce(v.resultat, 0) = v.objectif then 1 else 0 end franchie,
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

     ) t
     JOIN V_WF_ETAPE_PERTIN v ON t.these_id = v.these_id AND t.etape_id = v.etape_id
/

--
-- APP_WORKFLOW.ATTEIGNABLE() devient inutile, on le garde quand même un moment en souvenir.
--
create or replace PACKAGE APP_WORKFLOW AS

    /**
     * @deprecated depuis l'utilisation de 'dense_rank' pour calculer les témoins 'attegnable' et 'courant'
     * dans la vue V_WORKFLOW.
     */
    function atteignable(p_etape_id NUMERIC, p_these_id NUMERIC) return NUMERIC;

END APP_WORKFLOW;
/
create or replace PACKAGE BODY APP_WORKFLOW
AS

    /**
     * @deprecated depuis l'utilisation de 'dense_rank' pour calculer les témoins 'attegnable' et 'courant'
     * dans la vue V_WORKFLOW.
     */
    function atteignable(p_etape_id NUMERIC, p_these_id NUMERIC) return NUMERIC AS
        v_ordre numeric;
    BEGIN
        select ordre into v_ordre from wf_etape where id = p_etape_id;
        --DBMS_OUTPUT.PUT_LINE('ordre ' || v_ordre);
        for row in (
            select code, ORDRE, franchie, resultat, objectif
            from V_WORKFLOW v
            where v.these_id = p_these_id and v.ordre < v_ordre
            order by v.ordre
            ) loop
            --DBMS_OUTPUT.PUT_LINE(rpad(row.ordre, 5) || ' ' || row.code || ' : ' || row.franchie);
            if row.franchie = 0 then
                return 0;
            end if;
        end loop;

        RETURN 1;
    END atteignable;

END APP_WORKFLOW;
/
