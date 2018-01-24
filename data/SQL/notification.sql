-- ------------------------------------------------------------------------------------------------
--
-- Test Notification lorsque des thèses ont leur résultat qui passe à 1 à l'issu de la synchro.
--
-- ------------------------------------------------------------------------------------------------

--
-- 0/ Désactiver le job de synchro automatique.
--
BEGIN
  DBMS_SCHEDULER.disable(name => '"SODOCT"."synchronisation"', force => TRUE);
END;
/

--
-- 0/ Recherche des SOURCE_CODE des thèses.
--
select SOURCE_CODE from these where id in (25948);


-- 1/ Modif Vue src_these.
--
-- Simule que 2 thèses ont leur résultat qui passe à 1 à l'issu de la synchro :
--
-- ATTENTION !!
--   Script initial de la vue src_these A VERIFIER :
--   select text from all_views where view_name = 'SRC_THESE';
--
create or replace view src_these as
  with v as (
      -- script initial de la vue src_these A VERIFIER !
      select
        null id,
        thd.id thesard_id,
        ed.id ecole_doct_id,
        ur.id unite_rech_id,
        mv.lib_ths as titre,
        mv.eta_ths as etat_these,
        mv.LIB_INT1_DIS as lib_disc,
        mv.dat_deb_ths as date_prem_insc,
        mv.cod_eqr as cod_unit_rech,
        mv.lib_eqr as lib_unit_rech,
        mv.dat_prev_sou as date_prev_soutenance,
        mv.dat_sou_ths as date_soutenance,
        mv.dat_fin_cfd_ths as date_fin_confid,
        mv.lib_etab_cotut as lib_etab_cotut,
        mv.lib_pays_cotut as lib_pays_cotut,
        to_number(mv.cod_neg_tre) as resultat,
        mv.correction_possible as CORREC_AUTORISEE,
        mv.SOURCE_ID,
        mv.SOURCE_CODE
      from
        MV_THESE mv
        join THESARD thd on thd.SOURCE_CODE = mv.z_thesard_fk
        left join ecole_doct ed on ed.SOURCE_CODE = mv.z_ecole_doct_fk
        left join unite_rech ur on ur.SOURCE_CODE = mv.z_unite_rech_fk
      order by to_number(mv.SOURCE_CODE)
  )
  select * from v where SOURCE_CODE not in ('4783')
  union
  select
    ID,
    THESARD_ID,
    ECOLE_DOCT_ID,
    UNITE_RECH_ID,
    TITRE,
    ETAT_THESE,
    LIB_DISC,
    DATE_PREM_INSC,
    COD_UNIT_RECH,
    LIB_UNIT_RECH,
    DATE_PREV_SOUTENANCE,
    DATE_SOUTENANCE,
    DATE_FIN_CONFID,
    lib_etab_cotut,
    lib_pays_cotut,
    1 as RESULTAT,
    /*'mineure' as */CORREC_AUTORISEE,
    SOURCE_ID,
    SOURCE_CODE
  FROM v where SOURCE_CODE in ('4783')
--   union
--   select
--     ID,
--     THESARD_ID,
--     ECOLE_DOCT_ID,
--     UNITE_RECH_ID,
--     TITRE,
--     ETAT_THESE,
--     LIB_DISC,
--     DATE_PREM_INSC,
--     COD_UNIT_RECH,
--     LIB_UNIT_RECH,
--     DATE_PREV_SOUTENANCE,
--     DATE_SOUTENANCE,
--     DATE_FIN_CONFID,
--     lib_etab_cotut,
--     lib_pays_cotut,
--     1 as RESULTAT,
--     'majeure' as CORREC_AUTORISEE,
--     SOURCE_ID,
--     SOURCE_CODE
--   FROM v where SOURCE_CODE in ('12393')
;

--
-- 2/ Vérification que les 2 thèses apparaissent bien dans la vue diff.
--
select id, SOURCE_CODE, IMPORT_ACTION, CORREC_AUTORISEE, RESULTAT, U_CORREC_AUTORISEE, U_RESULTAT
from v_diff_these
where source_code in ('4783');

--
-- 3/ Lancement procédure.
--
begin sodoct_import.STORE_OBSERV_RESULTS; end; commit;
/

--
-- 4/ Vérification présence d'un résultat d'observation.
--
select ior.id, io.CODE, ior.DATE_CREATION, ior.SOURCE_CODE, ior.RESULTAT, ior.DATE_NOTIF
from IMPORT_OBSERV_RESULT ior
join IMPORT_OBSERV io on ior.IMPORT_OBSERV_ID = io.ID
where SOURCE_CODE = '4783'
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
  select
    null id,
    thd.id thesard_id,
    ed.id ecole_doct_id,
    ur.id unite_rech_id,
    mv.lib_ths as titre,
    mv.eta_ths as etat_these,
    mv.LIB_INT1_DIS as lib_disc,
    mv.dat_deb_ths as date_prem_insc,
    mv.cod_eqr as cod_unit_rech,
    mv.lib_eqr as lib_unit_rech,
    mv.dat_prev_sou as date_prev_soutenance,
    mv.dat_sou_ths as date_soutenance,
    mv.dat_fin_cfd_ths as date_fin_confid,
    mv.lib_etab_cotut as lib_etab_cotut,
    mv.lib_pays_cotut as lib_pays_cotut,
    to_number(mv.cod_neg_tre) as resultat,
    mv.correction_possible as CORREC_AUTORISEE,
    mv.SOURCE_ID,
    mv.SOURCE_CODE
  from
    MV_THESE mv
    join THESARD thd on thd.SOURCE_CODE = mv.z_thesard_fk
    left join ecole_doct ed on ed.SOURCE_CODE = mv.z_ecole_doct_fk
    left join unite_rech ur on ur.SOURCE_CODE = mv.z_unite_rech_fk
  order by to_number(mv.SOURCE_CODE)
  ;


--
-- 7/ Réactiver le job de synchro automatique.
--
BEGIN
  DBMS_SCHEDULER.enable(name=>'"SODOCT"."synchronisation"');
END;
/
