
--
-- Structure fermée
--

alter table STRUCTURE
    add EST_FERME number(1) default 0 ;

--
-- Id HAL dans le formulaire de diffusion.
--

alter table DIFFUSION add HAL_ID varchar2(100) ;

--
-- La remise d'un exemplaire papier dépend de l'autorisation de diffusion.
--

alter table DIFFUSION add VERSION_CORRIGEE NUMBER(1) default 0 not null;
alter table ATTESTATION modify EX_IMPR_CONFORM_VER_DEPO default null null;
alter table ATTESTATION add VERSION_CORRIGEE NUMBER(1) default 0 not null;
alter table RDV_BU modify EXEMPL_PAPIER_FOURNI default null null;

create or replace view V_WF_ETAPE_PERTIN as
select
    to_number(these_id) these_id,
    to_number(etape_id) etape_id,
    code,
    ordre,
    rownum id
from (
         --
         -- validation_page_de_couverture
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'VALIDATION_PAGE_DE_COUVERTURE'
         where t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- depot_version_originale
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'DEPOT_VERSION_ORIGINALE'
         where t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- autorisation_diffusion_these
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'AUTORISATION_DIFFUSION_THESE'
         where t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- attestations
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'ATTESTATIONS'
         where t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- signalement_these
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'SIGNALEMENT_THESE'
         where t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- archivabilite_version_originale
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'ARCHIVABILITE_VERSION_ORIGINALE'
         where t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- depot_version_archivage
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'DEPOT_VERSION_ARCHIVAGE'
                  join v_situ_archivab_vo situ on situ.these_id = t.id and situ.est_valide = 0 -- VO non archivable
         where t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- archivabilite_version_archivage
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE'
                  join v_situ_archivab_vo situ on situ.these_id = t.id and situ.est_valide = 0 -- VO non archivable
         where t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- verification_version_archivage
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'VERIFICATION_VERSION_ARCHIVAGE'
                  join v_situ_archivab_va situ on situ.these_id = t.id and situ.est_valide = 1 -- VA archivable
         where t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- rdv_bu_saisie_doctorant
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'RDV_BU_SAISIE_DOCTORANT'
         where t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- rdv_bu_saisie_bu
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'RDV_BU_SAISIE_BU'
         where t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- rdv_bu_validation_bu
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'RDV_BU_VALIDATION_BU'
         where t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues



         union all



         --
         -- depot_version_originale_corrigee
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'DEPOT_VERSION_ORIGINALE_CORRIGEE'
         where (t.correc_autorisee is not null or t.CORREC_AUTORISEE_FORCEE is not null) -- correction attendue
           and t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- autorisation_diffusion_these_version_corrigee
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE'
         where (t.correc_autorisee is not null or t.CORREC_AUTORISEE_FORCEE is not null) -- correction attendue
           and t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- attestations_version_corrigee
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'ATTESTATIONS_VERSION_CORRIGEE'
         where (t.correc_autorisee is not null or t.CORREC_AUTORISEE_FORCEE is not null) -- correction attendue
           and t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- archivabilite_version_originale_corrigee
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE'
         where (t.correc_autorisee is not null or t.CORREC_AUTORISEE_FORCEE is not null) -- correction attendue
           and t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- depot_version_archivage_corrigee
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE'
                  join v_situ_archivab_voc situ on situ.these_id = t.id and situ.est_valide = 0 -- VOC non archivable
         where (t.correc_autorisee is not null or t.CORREC_AUTORISEE_FORCEE is not null) -- correction attendue
           and t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- archivabilite_version_archivage_corrigee
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE'
                  join v_situ_archivab_voc situ on situ.these_id = t.id and situ.est_valide = 0 -- VOC non archivable
         where (t.correc_autorisee is not null or t.CORREC_AUTORISEE_FORCEE is not null) -- correction attendue
           and t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- verification_version_archivage_corrigee
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE'
                  join v_situ_archivab_vac situ on situ.these_id = t.id and situ.est_valide = 1 -- VAC archivable
         where (t.correc_autorisee is not null or t.CORREC_AUTORISEE_FORCEE is not null) -- correction attendue
           and t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- depot_version_corrigee_validation_doctorant
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT'
         where (t.correc_autorisee is not null or t.CORREC_AUTORISEE_FORCEE is not null) -- correction attendue
           and t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- depot_version_corrigee_validation_directeur
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR'
         where (t.correc_autorisee is not null or t.CORREC_AUTORISEE_FORCEE is not null) -- correction attendue
           and t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

         union all

         --
         -- REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE
         --
         select
             t.id as these_id,
             e.id as etape_id,
             e.code,
             e.ordre
         from these t
                  join wf_etape e on e.code = 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE'
                  join DIFFUSION d on d.VERSION_CORRIGEE = 1 and d.AUTORIS_MEL in (0/*Non*/, 1/*Oui+embargo*/) -- exemplaire papier requis
         where (t.correc_autorisee is not null or t.CORREC_AUTORISEE_FORCEE is not null) -- correction attendue
           and t.ETAT_THESE in ('E', 'S') -- thèses en cours ou soutenues

     )
;

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
         -- ATTESTATIONS : franchie si données saisies
         --
         SELECT
             t.id AS    these_id,
             e.id AS    etape_id,
             e.code,
             e.ORDRE,
             CASE WHEN v.attestation_id IS NULL THEN 0 ELSE 1 END franchie,
             CASE WHEN v.attestation_id IS NULL THEN 0 ELSE 1 END resultat,
             1          objectif
         FROM these t
                  JOIN WF_ETAPE e ON e.code = 'ATTESTATIONS'
                  LEFT JOIN V_SITU_ATTESTATIONS v ON v.these_id = t.id

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
             (CASE WHEN rdv.COORD_DOCTORANT IS NULL THEN 0 ELSE 1 END +
              CASE WHEN rdv.DISPO_DOCTORANT IS NULL THEN 0 ELSE 1 END) resultat,
             2                            objectif
         FROM these t
                  JOIN WF_ETAPE e ON e.code = 'RDV_BU_SAISIE_DOCTORANT'
                  LEFT JOIN V_SITU_RDV_BU_SAISIE_DOCT v ON v.these_id = t.id
                  LEFT JOIN RDV_BU rdv ON rdv.THESE_ID = t.id

         UNION ALL

         --
         -- RDV_BU_VALIDATION_BU : franchie si /*données BU saisies ET*/ une validation BU existe
         --
         SELECT
             t.id AS               these_id,
             e.id AS               etape_id,
             e.code,
             e.ORDRE,
             /*coalesce(vs.ok, 0) **/ coalesce(v.valide, 0) franchie,
             /*coalesce(vs.ok, 0) +*/ coalesce(v.valide, 0) resultat,
             /*2*/1 objectif
         FROM these t
                  JOIN WF_ETAPE e ON e.code = 'RDV_BU_VALIDATION_BU'
             --LEFT JOIN V_SITU_RDV_BU_SAISIE_BU vs ON vs.these_id = t.id
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
;

create or replace view V_SITU_AUTORIS_DIFF_THESE as
SELECT
    d.these_id,
    d.id AS diffusion_id
FROM DIFFUSION d
where d.VERSION_CORRIGEE = 0 and d.HISTO_DESTRUCTEUR_ID is null
;

create or replace view V_SITU_AUTORIS_DIFF_THESE_VOC as
SELECT
    d.these_id,
    d.id AS diffusion_id
FROM DIFFUSION d
         -- NB: on se base sur l'existence d'une version corrigée et pas sur t.CORRECTION_AUTORISEE qui peut revenir à null
         JOIN FICHIER_THESE ft ON ft.THESE_ID = d.THESE_ID AND EST_ANNEXE = 0 AND EST_EXPURGE = 0
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
where d.VERSION_CORRIGEE = 1 and d.HISTO_DESTRUCTEUR_ID is null
;

create or replace view V_SITU_ATTESTATIONS as
SELECT
    a.these_id,
    a.id AS attestation_id
FROM ATTESTATION a
where a.VERSION_CORRIGEE = 0 and a.HISTO_DESTRUCTEUR_ID is null
;

create or replace view V_SITU_ATTESTATIONS_VOC as
SELECT
    a.these_id,
    a.id AS attestation_id
FROM ATTESTATION a
         -- NB: on se base sur l'existence d'une version corrigée et pas sur t.CORRECTION_AUTORISEE qui peut revenir à null
         JOIN FICHIER_THESE ft ON ft.THESE_ID = a.THESE_ID AND EST_ANNEXE = 0 AND EST_EXPURGE = 0
         JOIN FICHIER f ON ft.FICHIER_ID = f.id and f.HISTO_DESTRUCTION is null
         JOIN VERSION_FICHIER v ON f.VERSION_FICHIER_ID = v.id AND v.CODE = 'VOC'
where a.VERSION_CORRIGEE = 1 and a.HISTO_DESTRUCTEUR_ID is null
;
