
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



--
-- Ajout des dates d'abandon et de transfert.
--

alter table TMP_THESE
    add DAT_ABANDON date
/
alter table TMP_THESE
    add DAT_TRANSFERT_DEP date
/
alter table THESE
    add DATE_ABANDON date
/
alter table THESE
    add DATE_TRANSFERT date
/

create or replace view SRC_THESE as
select
    null                            as id,
    tmp.source_code                 as source_code,
    src.id                          as source_id,
    e.id                            as etablissement_id,
    d.id                            as doctorant_id,
    coalesce(ed_substit.id, ed.id)  as ecole_doct_id,
    coalesce(ur_substit.id, ur.id)  as unite_rech_id,
    ed.id                           as ecole_doct_id_orig,
    ur.id                           as unite_rech_id_orig,
    tmp.lib_ths                     as titre,
    tmp.eta_ths                     as etat_these,
    to_number(tmp.cod_neg_tre)      as resultat,
    tmp.lib_int1_dis                as lib_disc,
    tmp.dat_deb_ths                 as date_prem_insc,
    tmp.ANNEE_UNIV_1ERE_INSC        as annee_univ_1ere_insc, -- deprecated
    tmp.dat_prev_sou                as date_prev_soutenance,
    tmp.dat_sou_ths                 as date_soutenance,
    tmp.dat_fin_cfd_ths             as date_fin_confid,
    tmp.lib_etab_cotut              as lib_etab_cotut,
    tmp.lib_pays_cotut              as lib_pays_cotut,
    tmp.correction_possible         as correc_autorisee,
    tem_sou_aut_ths                 as soutenance_autoris,
    dat_aut_sou_ths                 as date_autoris_soutenance,
    tem_avenant_cotut               as tem_avenant_cotut,
    dat_abandon                     as date_abandon,
    dat_transfert_dep               as date_transfert
from tmp_these tmp
         JOIN STRUCTURE s ON s.SOURCE_CODE = tmp.ETABLISSEMENT_ID
         join etablissement e on e.structure_id = s.id
         join source src on src.code = tmp.source_id
         join doctorant d on d.source_code = tmp.doctorant_id
         left join ecole_doct ed on ed.source_code = tmp.ecole_doct_id
         left join unite_rech ur on ur.source_code = tmp.unite_rech_id
         left join structure_substit ss_ed on ss_ed.from_structure_id = ed.structure_id
         left join ecole_doct ed_substit on ed_substit.structure_id = ss_ed.to_structure_id
         left join structure_substit ss_ur on ss_ur.from_structure_id = ur.structure_id
         left join unite_rech ur_substit on ur_substit.structure_id = ss_ur.to_structure_id
/

create or replace view V_DIFF_THESE as
select diff."ID",diff."SOURCE_ID",diff."SOURCE_CODE",diff."IMPORT_ACTION",diff."ANNEE_UNIV_1ERE_INSC",diff."CORREC_AUTORISEE",diff."DATE_ABANDON",diff."DATE_AUTORIS_SOUTENANCE",diff."DATE_FIN_CONFID",diff."DATE_PREM_INSC",diff."DATE_PREV_SOUTENANCE",diff."DATE_SOUTENANCE",diff."DATE_TRANSFERT",diff."DOCTORANT_ID",diff."ECOLE_DOCT_ID",diff."ETABLISSEMENT_ID",diff."ETAT_THESE",diff."LIB_DISC",diff."LIB_ETAB_COTUT",diff."LIB_PAYS_COTUT",diff."RESULTAT",diff."SOUTENANCE_AUTORIS",diff."TEM_AVENANT_COTUT",diff."TITRE",diff."UNITE_RECH_ID",diff."U_ANNEE_UNIV_1ERE_INSC",diff."U_CORREC_AUTORISEE",diff."U_DATE_ABANDON",diff."U_DATE_AUTORIS_SOUTENANCE",diff."U_DATE_FIN_CONFID",diff."U_DATE_PREM_INSC",diff."U_DATE_PREV_SOUTENANCE",diff."U_DATE_SOUTENANCE",diff."U_DATE_TRANSFERT",diff."U_DOCTORANT_ID",diff."U_ECOLE_DOCT_ID",diff."U_ETABLISSEMENT_ID",diff."U_ETAT_THESE",diff."U_LIB_DISC",diff."U_LIB_ETAB_COTUT",diff."U_LIB_PAYS_COTUT",diff."U_RESULTAT",diff."U_SOUTENANCE_AUTORIS",diff."U_TEM_AVENANT_COTUT",diff."U_TITRE",diff."U_UNITE_RECH_ID" from (SELECT
  COALESCE( D.id, S.id ) id,
  COALESCE( S.source_id, D.source_id ) source_id,
  COALESCE( S.source_code, D.source_code ) source_code,
CASE
    WHEN S.source_code IS NOT NULL AND D.source_code IS NULL THEN 'insert'
    WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'update'
    WHEN S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE) THEN 'delete'
    WHEN S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE THEN 'undelete' END import_action,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.ANNEE_UNIV_1ERE_INSC ELSE S.ANNEE_UNIV_1ERE_INSC END ANNEE_UNIV_1ERE_INSC,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.CORREC_AUTORISEE ELSE S.CORREC_AUTORISEE END CORREC_AUTORISEE,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_ABANDON ELSE S.DATE_ABANDON END DATE_ABANDON,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_AUTORIS_SOUTENANCE ELSE S.DATE_AUTORIS_SOUTENANCE END DATE_AUTORIS_SOUTENANCE,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_FIN_CONFID ELSE S.DATE_FIN_CONFID END DATE_FIN_CONFID,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_PREM_INSC ELSE S.DATE_PREM_INSC END DATE_PREM_INSC,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_PREV_SOUTENANCE ELSE S.DATE_PREV_SOUTENANCE END DATE_PREV_SOUTENANCE,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_SOUTENANCE ELSE S.DATE_SOUTENANCE END DATE_SOUTENANCE,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DATE_TRANSFERT ELSE S.DATE_TRANSFERT END DATE_TRANSFERT,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.DOCTORANT_ID ELSE S.DOCTORANT_ID END DOCTORANT_ID,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.ECOLE_DOCT_ID ELSE S.ECOLE_DOCT_ID END ECOLE_DOCT_ID,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.ETABLISSEMENT_ID ELSE S.ETABLISSEMENT_ID END ETABLISSEMENT_ID,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.ETAT_THESE ELSE S.ETAT_THESE END ETAT_THESE,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.LIB_DISC ELSE S.LIB_DISC END LIB_DISC,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.LIB_ETAB_COTUT ELSE S.LIB_ETAB_COTUT END LIB_ETAB_COTUT,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.LIB_PAYS_COTUT ELSE S.LIB_PAYS_COTUT END LIB_PAYS_COTUT,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.RESULTAT ELSE S.RESULTAT END RESULTAT,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.SOUTENANCE_AUTORIS ELSE S.SOUTENANCE_AUTORIS END SOUTENANCE_AUTORIS,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.TEM_AVENANT_COTUT ELSE S.TEM_AVENANT_COTUT END TEM_AVENANT_COTUT,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.TITRE ELSE S.TITRE END TITRE,
    CASE WHEN S.source_code IS NULL AND D.source_code IS NOT NULL THEN D.UNITE_RECH_ID ELSE S.UNITE_RECH_ID END UNITE_RECH_ID,
    CASE WHEN D.ANNEE_UNIV_1ERE_INSC <> S.ANNEE_UNIV_1ERE_INSC OR (D.ANNEE_UNIV_1ERE_INSC IS NULL AND S.ANNEE_UNIV_1ERE_INSC IS NOT NULL) OR (D.ANNEE_UNIV_1ERE_INSC IS NOT NULL AND S.ANNEE_UNIV_1ERE_INSC IS NULL) THEN 1 ELSE 0 END U_ANNEE_UNIV_1ERE_INSC,
    CASE WHEN D.CORREC_AUTORISEE <> S.CORREC_AUTORISEE OR (D.CORREC_AUTORISEE IS NULL AND S.CORREC_AUTORISEE IS NOT NULL) OR (D.CORREC_AUTORISEE IS NOT NULL AND S.CORREC_AUTORISEE IS NULL) THEN 1 ELSE 0 END U_CORREC_AUTORISEE,
    CASE WHEN D.DATE_ABANDON <> S.DATE_ABANDON OR (D.DATE_ABANDON IS NULL AND S.DATE_ABANDON IS NOT NULL) OR (D.DATE_ABANDON IS NOT NULL AND S.DATE_ABANDON IS NULL) THEN 1 ELSE 0 END U_DATE_ABANDON,
    CASE WHEN D.DATE_AUTORIS_SOUTENANCE <> S.DATE_AUTORIS_SOUTENANCE OR (D.DATE_AUTORIS_SOUTENANCE IS NULL AND S.DATE_AUTORIS_SOUTENANCE IS NOT NULL) OR (D.DATE_AUTORIS_SOUTENANCE IS NOT NULL AND S.DATE_AUTORIS_SOUTENANCE IS NULL) THEN 1 ELSE 0 END U_DATE_AUTORIS_SOUTENANCE,
    CASE WHEN D.DATE_FIN_CONFID <> S.DATE_FIN_CONFID OR (D.DATE_FIN_CONFID IS NULL AND S.DATE_FIN_CONFID IS NOT NULL) OR (D.DATE_FIN_CONFID IS NOT NULL AND S.DATE_FIN_CONFID IS NULL) THEN 1 ELSE 0 END U_DATE_FIN_CONFID,
    CASE WHEN D.DATE_PREM_INSC <> S.DATE_PREM_INSC OR (D.DATE_PREM_INSC IS NULL AND S.DATE_PREM_INSC IS NOT NULL) OR (D.DATE_PREM_INSC IS NOT NULL AND S.DATE_PREM_INSC IS NULL) THEN 1 ELSE 0 END U_DATE_PREM_INSC,
    CASE WHEN D.DATE_PREV_SOUTENANCE <> S.DATE_PREV_SOUTENANCE OR (D.DATE_PREV_SOUTENANCE IS NULL AND S.DATE_PREV_SOUTENANCE IS NOT NULL) OR (D.DATE_PREV_SOUTENANCE IS NOT NULL AND S.DATE_PREV_SOUTENANCE IS NULL) THEN 1 ELSE 0 END U_DATE_PREV_SOUTENANCE,
    CASE WHEN D.DATE_SOUTENANCE <> S.DATE_SOUTENANCE OR (D.DATE_SOUTENANCE IS NULL AND S.DATE_SOUTENANCE IS NOT NULL) OR (D.DATE_SOUTENANCE IS NOT NULL AND S.DATE_SOUTENANCE IS NULL) THEN 1 ELSE 0 END U_DATE_SOUTENANCE,
    CASE WHEN D.DATE_TRANSFERT <> S.DATE_TRANSFERT OR (D.DATE_TRANSFERT IS NULL AND S.DATE_TRANSFERT IS NOT NULL) OR (D.DATE_TRANSFERT IS NOT NULL AND S.DATE_TRANSFERT IS NULL) THEN 1 ELSE 0 END U_DATE_TRANSFERT,
    CASE WHEN D.DOCTORANT_ID <> S.DOCTORANT_ID OR (D.DOCTORANT_ID IS NULL AND S.DOCTORANT_ID IS NOT NULL) OR (D.DOCTORANT_ID IS NOT NULL AND S.DOCTORANT_ID IS NULL) THEN 1 ELSE 0 END U_DOCTORANT_ID,
    CASE WHEN D.ECOLE_DOCT_ID <> S.ECOLE_DOCT_ID OR (D.ECOLE_DOCT_ID IS NULL AND S.ECOLE_DOCT_ID IS NOT NULL) OR (D.ECOLE_DOCT_ID IS NOT NULL AND S.ECOLE_DOCT_ID IS NULL) THEN 1 ELSE 0 END U_ECOLE_DOCT_ID,
    CASE WHEN D.ETABLISSEMENT_ID <> S.ETABLISSEMENT_ID OR (D.ETABLISSEMENT_ID IS NULL AND S.ETABLISSEMENT_ID IS NOT NULL) OR (D.ETABLISSEMENT_ID IS NOT NULL AND S.ETABLISSEMENT_ID IS NULL) THEN 1 ELSE 0 END U_ETABLISSEMENT_ID,
    CASE WHEN D.ETAT_THESE <> S.ETAT_THESE OR (D.ETAT_THESE IS NULL AND S.ETAT_THESE IS NOT NULL) OR (D.ETAT_THESE IS NOT NULL AND S.ETAT_THESE IS NULL) THEN 1 ELSE 0 END U_ETAT_THESE,
    CASE WHEN D.LIB_DISC <> S.LIB_DISC OR (D.LIB_DISC IS NULL AND S.LIB_DISC IS NOT NULL) OR (D.LIB_DISC IS NOT NULL AND S.LIB_DISC IS NULL) THEN 1 ELSE 0 END U_LIB_DISC,
    CASE WHEN D.LIB_ETAB_COTUT <> S.LIB_ETAB_COTUT OR (D.LIB_ETAB_COTUT IS NULL AND S.LIB_ETAB_COTUT IS NOT NULL) OR (D.LIB_ETAB_COTUT IS NOT NULL AND S.LIB_ETAB_COTUT IS NULL) THEN 1 ELSE 0 END U_LIB_ETAB_COTUT,
    CASE WHEN D.LIB_PAYS_COTUT <> S.LIB_PAYS_COTUT OR (D.LIB_PAYS_COTUT IS NULL AND S.LIB_PAYS_COTUT IS NOT NULL) OR (D.LIB_PAYS_COTUT IS NOT NULL AND S.LIB_PAYS_COTUT IS NULL) THEN 1 ELSE 0 END U_LIB_PAYS_COTUT,
    CASE WHEN D.RESULTAT <> S.RESULTAT OR (D.RESULTAT IS NULL AND S.RESULTAT IS NOT NULL) OR (D.RESULTAT IS NOT NULL AND S.RESULTAT IS NULL) THEN 1 ELSE 0 END U_RESULTAT,
    CASE WHEN D.SOUTENANCE_AUTORIS <> S.SOUTENANCE_AUTORIS OR (D.SOUTENANCE_AUTORIS IS NULL AND S.SOUTENANCE_AUTORIS IS NOT NULL) OR (D.SOUTENANCE_AUTORIS IS NOT NULL AND S.SOUTENANCE_AUTORIS IS NULL) THEN 1 ELSE 0 END U_SOUTENANCE_AUTORIS,
    CASE WHEN D.TEM_AVENANT_COTUT <> S.TEM_AVENANT_COTUT OR (D.TEM_AVENANT_COTUT IS NULL AND S.TEM_AVENANT_COTUT IS NOT NULL) OR (D.TEM_AVENANT_COTUT IS NOT NULL AND S.TEM_AVENANT_COTUT IS NULL) THEN 1 ELSE 0 END U_TEM_AVENANT_COTUT,
    CASE WHEN D.TITRE <> S.TITRE OR (D.TITRE IS NULL AND S.TITRE IS NOT NULL) OR (D.TITRE IS NOT NULL AND S.TITRE IS NULL) THEN 1 ELSE 0 END U_TITRE,
    CASE WHEN D.UNITE_RECH_ID <> S.UNITE_RECH_ID OR (D.UNITE_RECH_ID IS NULL AND S.UNITE_RECH_ID IS NOT NULL) OR (D.UNITE_RECH_ID IS NOT NULL AND S.UNITE_RECH_ID IS NULL) THEN 1 ELSE 0 END U_UNITE_RECH_ID
FROM
  THESE D
  FULL JOIN SRC_THESE S ON S.source_id = D.source_id AND S.source_code = D.source_code
WHERE
       (S.source_code IS NOT NULL AND D.source_code IS NOT NULL AND D.histo_destruction IS NOT NULL AND D.histo_destruction <= SYSDATE)
    OR (S.source_code IS NULL AND D.source_code IS NOT NULL AND (D.histo_destruction IS NULL OR D.histo_destruction > SYSDATE))
    OR (S.source_code IS NOT NULL AND D.source_code IS NULL)
    OR D.ANNEE_UNIV_1ERE_INSC <> S.ANNEE_UNIV_1ERE_INSC OR (D.ANNEE_UNIV_1ERE_INSC IS NULL AND S.ANNEE_UNIV_1ERE_INSC IS NOT NULL) OR (D.ANNEE_UNIV_1ERE_INSC IS NOT NULL AND S.ANNEE_UNIV_1ERE_INSC IS NULL)
  OR D.CORREC_AUTORISEE <> S.CORREC_AUTORISEE OR (D.CORREC_AUTORISEE IS NULL AND S.CORREC_AUTORISEE IS NOT NULL) OR (D.CORREC_AUTORISEE IS NOT NULL AND S.CORREC_AUTORISEE IS NULL)
  OR D.DATE_ABANDON <> S.DATE_ABANDON OR (D.DATE_ABANDON IS NULL AND S.DATE_ABANDON IS NOT NULL) OR (D.DATE_ABANDON IS NOT NULL AND S.DATE_ABANDON IS NULL)
  OR D.DATE_AUTORIS_SOUTENANCE <> S.DATE_AUTORIS_SOUTENANCE OR (D.DATE_AUTORIS_SOUTENANCE IS NULL AND S.DATE_AUTORIS_SOUTENANCE IS NOT NULL) OR (D.DATE_AUTORIS_SOUTENANCE IS NOT NULL AND S.DATE_AUTORIS_SOUTENANCE IS NULL)
  OR D.DATE_FIN_CONFID <> S.DATE_FIN_CONFID OR (D.DATE_FIN_CONFID IS NULL AND S.DATE_FIN_CONFID IS NOT NULL) OR (D.DATE_FIN_CONFID IS NOT NULL AND S.DATE_FIN_CONFID IS NULL)
  OR D.DATE_PREM_INSC <> S.DATE_PREM_INSC OR (D.DATE_PREM_INSC IS NULL AND S.DATE_PREM_INSC IS NOT NULL) OR (D.DATE_PREM_INSC IS NOT NULL AND S.DATE_PREM_INSC IS NULL)
  OR D.DATE_PREV_SOUTENANCE <> S.DATE_PREV_SOUTENANCE OR (D.DATE_PREV_SOUTENANCE IS NULL AND S.DATE_PREV_SOUTENANCE IS NOT NULL) OR (D.DATE_PREV_SOUTENANCE IS NOT NULL AND S.DATE_PREV_SOUTENANCE IS NULL)
  OR D.DATE_SOUTENANCE <> S.DATE_SOUTENANCE OR (D.DATE_SOUTENANCE IS NULL AND S.DATE_SOUTENANCE IS NOT NULL) OR (D.DATE_SOUTENANCE IS NOT NULL AND S.DATE_SOUTENANCE IS NULL)
  OR D.DATE_TRANSFERT <> S.DATE_TRANSFERT OR (D.DATE_TRANSFERT IS NULL AND S.DATE_TRANSFERT IS NOT NULL) OR (D.DATE_TRANSFERT IS NOT NULL AND S.DATE_TRANSFERT IS NULL)
  OR D.DOCTORANT_ID <> S.DOCTORANT_ID OR (D.DOCTORANT_ID IS NULL AND S.DOCTORANT_ID IS NOT NULL) OR (D.DOCTORANT_ID IS NOT NULL AND S.DOCTORANT_ID IS NULL)
  OR D.ECOLE_DOCT_ID <> S.ECOLE_DOCT_ID OR (D.ECOLE_DOCT_ID IS NULL AND S.ECOLE_DOCT_ID IS NOT NULL) OR (D.ECOLE_DOCT_ID IS NOT NULL AND S.ECOLE_DOCT_ID IS NULL)
  OR D.ETABLISSEMENT_ID <> S.ETABLISSEMENT_ID OR (D.ETABLISSEMENT_ID IS NULL AND S.ETABLISSEMENT_ID IS NOT NULL) OR (D.ETABLISSEMENT_ID IS NOT NULL AND S.ETABLISSEMENT_ID IS NULL)
  OR D.ETAT_THESE <> S.ETAT_THESE OR (D.ETAT_THESE IS NULL AND S.ETAT_THESE IS NOT NULL) OR (D.ETAT_THESE IS NOT NULL AND S.ETAT_THESE IS NULL)
  OR D.LIB_DISC <> S.LIB_DISC OR (D.LIB_DISC IS NULL AND S.LIB_DISC IS NOT NULL) OR (D.LIB_DISC IS NOT NULL AND S.LIB_DISC IS NULL)
  OR D.LIB_ETAB_COTUT <> S.LIB_ETAB_COTUT OR (D.LIB_ETAB_COTUT IS NULL AND S.LIB_ETAB_COTUT IS NOT NULL) OR (D.LIB_ETAB_COTUT IS NOT NULL AND S.LIB_ETAB_COTUT IS NULL)
  OR D.LIB_PAYS_COTUT <> S.LIB_PAYS_COTUT OR (D.LIB_PAYS_COTUT IS NULL AND S.LIB_PAYS_COTUT IS NOT NULL) OR (D.LIB_PAYS_COTUT IS NOT NULL AND S.LIB_PAYS_COTUT IS NULL)
  OR D.RESULTAT <> S.RESULTAT OR (D.RESULTAT IS NULL AND S.RESULTAT IS NOT NULL) OR (D.RESULTAT IS NOT NULL AND S.RESULTAT IS NULL)
  OR D.SOUTENANCE_AUTORIS <> S.SOUTENANCE_AUTORIS OR (D.SOUTENANCE_AUTORIS IS NULL AND S.SOUTENANCE_AUTORIS IS NOT NULL) OR (D.SOUTENANCE_AUTORIS IS NOT NULL AND S.SOUTENANCE_AUTORIS IS NULL)
  OR D.TEM_AVENANT_COTUT <> S.TEM_AVENANT_COTUT OR (D.TEM_AVENANT_COTUT IS NULL AND S.TEM_AVENANT_COTUT IS NOT NULL) OR (D.TEM_AVENANT_COTUT IS NOT NULL AND S.TEM_AVENANT_COTUT IS NULL)
  OR D.TITRE <> S.TITRE OR (D.TITRE IS NULL AND S.TITRE IS NOT NULL) OR (D.TITRE IS NOT NULL AND S.TITRE IS NULL)
  OR D.UNITE_RECH_ID <> S.UNITE_RECH_ID OR (D.UNITE_RECH_ID IS NULL AND S.UNITE_RECH_ID IS NOT NULL) OR (D.UNITE_RECH_ID IS NOT NULL AND S.UNITE_RECH_ID IS NULL)
) diff JOIN source on source.id = diff.source_id WHERE import_action IS NOT NULL AND source.importable = 1
/
