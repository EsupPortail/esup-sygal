-- ------------------------------------------------------------------------------------------------
--
-- Test Notification lorsque des thèses ont leur résultat qui passe à 1 à l'issu de la synchro.
--
-- ------------------------------------------------------------------------------------------------

--
-- 0/ Désactiver la synchro CRONée.
--

--
-- 0/ Recherche des SOURCE_CODE des thèses.
--
select SOURCE_CODE from these where id in (25948);


-- 1/ Modif Vue src_these.
--
-- Simule qu'une thèse a son résultat qui passe à 1 à l'issu de la synchro :
--
-- ATTENTION !!
--   Script initial de la vue src_these A VERIFIER :
--   select text from all_views where view_name = 'SRC_THESE';
--
create or replace view src_these as
  with v as (
      -- script initial de la vue src_these A VERIFIER !
      SELECT
        NULL                            AS id,
        tmp.SOURCE_CODE                 AS SOURCE_CODE,
        src.ID                          AS source_id,
        e.id                            AS etablissement_id,
        d.id                            AS doctorant_id,
        coalesce(ed_substit.id, ed.id)  AS ecole_doct_id,
        coalesce(ur_substit.id, ur.id)  AS unite_rech_id,
        --     ed.id  AS ecole_doct_id,
        --     ur.id  AS unite_rech_id,
        ed.id                           AS ecole_doct_id_orig,
        ur.id                           AS unite_rech_id_orig,
        tmp.lib_ths                     AS titre,
        tmp.eta_ths                     AS etat_these,
        to_number(tmp.cod_neg_tre)      AS resultat,
        tmp.lib_int1_dis                AS lib_disc,
        tmp.dat_deb_ths                 AS date_prem_insc,
        tmp.dat_prev_sou                AS date_prev_soutenance,
        tmp.dat_sou_ths                 AS date_soutenance,
        tmp.dat_fin_cfd_ths             AS date_fin_confid,
        tmp.lib_etab_cotut              AS lib_etab_cotut,
        tmp.lib_pays_cotut              AS lib_pays_cotut,
        tmp.correction_possible         AS CORREC_AUTORISEE,
        tem_sou_aut_ths                 AS soutenance_autoris,
        dat_aut_sou_ths                 AS date_autoris_soutenance,
        tem_avenant_cotut               AS tem_avenant_cotut
      FROM TMP_THESE tmp
        JOIN STRUCTURE s ON s.CODE = tmp.ETABLISSEMENT_ID
        JOIN ETABLISSEMENT e ON e.STRUCTURE_ID = s.ID
        JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
        JOIN DOCTORANT d ON d.SOURCE_CODE = tmp.DOCTORANT_ID

        LEFT JOIN ECOLE_DOCT ed ON ed.SOURCE_CODE = tmp.ECOLE_DOCT_ID
        LEFT JOIN UNITE_RECH ur ON ur.SOURCE_CODE = tmp.UNITE_RECH_ID

        LEFT JOIN STRUCTURE_SUBSTIT ss_ed on ss_ed.FROM_STRUCTURE_ID = ed.STRUCTURE_ID
        LEFT JOIN ECOLE_DOCT ed_substit on ed_substit.STRUCTURE_ID = ss_ed.TO_STRUCTURE_ID

        LEFT JOIN STRUCTURE_SUBSTIT ss_ur on ss_ur.FROM_STRUCTURE_ID = ur.STRUCTURE_ID
        LEFT JOIN UNITE_RECH ur_substit on ur_substit.STRUCTURE_ID = ss_ur.TO_STRUCTURE_ID
  )
  select * from v where SOURCE_CODE not in ('UCN::4783')
  union
  select
    ID,
    SOURCE_CODE,
    SOURCE_ID,
    ETABLISSEMENT_ID,
    DOCTORANT_ID,
    ECOLE_DOCT_ID,
    UNITE_RECH_ID,
    ECOLE_DOCT_ID_ORIG,
    UNITE_RECH_ID_ORIG,
    TITRE,
    ETAT_THESE,
    1 as RESULTAT,
    LIB_DISC,
    DATE_PREM_INSC,
    DATE_PREV_SOUTENANCE,
    DATE_SOUTENANCE,
    DATE_FIN_CONFID,
    LIB_ETAB_COTUT,
    LIB_PAYS_COTUT,
    CORREC_AUTORISEE,
    SOUTENANCE_AUTORIS,
    DATE_AUTORIS_SOUTENANCE,
    TEM_AVENANT_COTUT
  FROM v where SOURCE_CODE in ('UCN::4783')
;

--
-- 2/ Vérification que les thèses apparaissent bien dans la vue diff.
--
select id, SOURCE_CODE, IMPORT_ACTION, CORREC_AUTORISEE, RESULTAT, U_CORREC_AUTORISEE, U_RESULTAT
from v_diff_these
where source_code in ('UCN::4783');

--
-- 3/ Lancement procédure.
--
begin app_import.STORE_OBSERV_RESULTS; end; commit;
/

--
-- 4/ Vérification présence d'un résultat d'observation.
--
select ior.id, io.CODE, ior.DATE_CREATION, ior.SOURCE_CODE, ior.RESULTAT, ior.DATE_NOTIF
from IMPORT_OBSERV_RESULT ior
join IMPORT_OBSERV io on ior.IMPORT_OBSERV_ID = io.ID
where SOURCE_CODE = 'UCN::4783'
order by DATE_CREATION desc
;

--
-- 5/ Lancer le script PHP de traitement des résultats d'observation :
--    un mail devrait être envoyé...
--
-- $ php public/index.php process-observed-import-results


--
-- 6/ Restauration Vue src_these initiale.
--
create or replace view src_these as
  SELECT
    NULL                            AS id,
    tmp.SOURCE_CODE                 AS SOURCE_CODE,
    src.ID                          AS source_id,
    e.id                            AS etablissement_id,
    d.id                            AS doctorant_id,
    coalesce(ed_substit.id, ed.id)  AS ecole_doct_id,
    coalesce(ur_substit.id, ur.id)  AS unite_rech_id,
    --     ed.id  AS ecole_doct_id,
    --     ur.id  AS unite_rech_id,
    ed.id                           AS ecole_doct_id_orig,
    ur.id                           AS unite_rech_id_orig,
    tmp.lib_ths                     AS titre,
    tmp.eta_ths                     AS etat_these,
    to_number(tmp.cod_neg_tre)      AS resultat,
    tmp.lib_int1_dis                AS lib_disc,
    tmp.dat_deb_ths                 AS date_prem_insc,
    tmp.dat_prev_sou                AS date_prev_soutenance,
    tmp.dat_sou_ths                 AS date_soutenance,
    tmp.dat_fin_cfd_ths             AS date_fin_confid,
    tmp.lib_etab_cotut              AS lib_etab_cotut,
    tmp.lib_pays_cotut              AS lib_pays_cotut,
    tmp.correction_possible         AS CORREC_AUTORISEE,
    tem_sou_aut_ths                 AS soutenance_autoris,
    dat_aut_sou_ths                 AS date_autoris_soutenance,
    tem_avenant_cotut               AS tem_avenant_cotut
  FROM TMP_THESE tmp
    JOIN STRUCTURE s ON s.CODE = tmp.ETABLISSEMENT_ID
    JOIN ETABLISSEMENT e ON e.STRUCTURE_ID = s.ID
    JOIN SOURCE src ON src.CODE = tmp.SOURCE_ID
    JOIN DOCTORANT d ON d.SOURCE_CODE = tmp.DOCTORANT_ID

    LEFT JOIN ECOLE_DOCT ed ON ed.SOURCE_CODE = tmp.ECOLE_DOCT_ID
    LEFT JOIN UNITE_RECH ur ON ur.SOURCE_CODE = tmp.UNITE_RECH_ID

    LEFT JOIN STRUCTURE_SUBSTIT ss_ed on ss_ed.FROM_STRUCTURE_ID = ed.STRUCTURE_ID
    LEFT JOIN ECOLE_DOCT ed_substit on ed_substit.STRUCTURE_ID = ss_ed.TO_STRUCTURE_ID

    LEFT JOIN STRUCTURE_SUBSTIT ss_ur on ss_ur.FROM_STRUCTURE_ID = ur.STRUCTURE_ID
    LEFT JOIN UNITE_RECH ur_substit on ur_substit.STRUCTURE_ID = ss_ur.TO_STRUCTURE_ID
  ;


--
-- 7/ Réactiver la synchro CRONée.
--
