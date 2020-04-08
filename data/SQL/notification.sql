-- ------------------------------------------------------------------------------------------------
--
-- Test Notification lorsque des thèses ont leur résultat qui passe à 1 à l'issu de la synchro.
--
-- ------------------------------------------------------------------------------------------------

--
-- 1/ Désactiver l'import et l'observation des résultats d'import CRONés.
--
-- Ex: ssh root@host.domain.fr 'chmod -x /etc/cron.d/sygal'

--
-- 2/ Recherche d'une thèse dont le RESULTAT est à NULL.
--
select SOURCE_CODE from these where RESULTAT is null order by id desc;


-- 3/ Modif Vue src_these.
--
-- Simule qu'une thèse a son résultat qui passe à 1 à l'issu de la synchro :
--
-- ATTENTION !!
--   Script initial de la vue src_these A VERIFIER :
--   select text from all_views where view_name = 'SRC_THESE';
--
create or replace view src_these as
  with
   t as (
       select 'UCN::16635' these_source_code from dual
   ),
   v as (
      -- script initial de la vue src_these A VERIFIER !
      --create or replace view SRC_THESE as
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
)
  select
      id,
      source_code,
      source_id,
      etablissement_id,
      doctorant_id,
      ecole_doct_id,
      unite_rech_id,
      ecole_doct_id_orig,
      unite_rech_id_orig,
      titre,
      etat_these,
      lib_disc,
      date_prem_insc,
      annee_univ_1ere_insc,
      date_prev_soutenance,
      date_soutenance,
      date_fin_confid,
      lib_etab_cotut,
      lib_pays_cotut,
      correc_autorisee,
      soutenance_autoris,
      date_autoris_soutenance,
      tem_avenant_cotut,
      date_abandon,
      date_transfert,
      1 as resultat
  from t, v where source_code in (these_source_code)
union
  select
      id,
      source_code,
      source_id,
      etablissement_id,
      doctorant_id,
      ecole_doct_id,
      unite_rech_id,
      ecole_doct_id_orig,
      unite_rech_id_orig,
      titre,
      etat_these,
      lib_disc,
      date_prem_insc,
      annee_univ_1ere_insc,
      date_prev_soutenance,
      date_soutenance,
      date_fin_confid,
      lib_etab_cotut,
      lib_pays_cotut,
      correc_autorisee,
      soutenance_autoris,
      date_autoris_soutenance,
      tem_avenant_cotut,
      date_abandon,
      date_transfert,
      resultat
  from t, v where source_code not in (these_source_code)
;

--
-- 4/ Vérification que les thèses apparaissent bien dans la vue diff.
--
select id, SOURCE_CODE, IMPORT_ACTION, CORREC_AUTORISEE, RESULTAT, U_CORREC_AUTORISEE, U_RESULTAT
from v_diff_these
where source_code in ('UCN::16635');

--
-- 5/ Lancement procédure.
--
begin app_import.STORE_OBSERV_RESULTS; end; commit;
/

--
-- 6/ Vérification présence d'un résultat d'observation.
--
select ioer.id, io.CODE, ioer.DATE_CREATION, ioer.SOURCE_CODE, ioer.RESULTAT, ioer.DATE_NOTIF, ioer.TOO_OLD
from IMPORT_OBSERV_ETAB_RESULT ioer
join IMPORT_OBSERV_ETAB ioe on ioer.IMPORT_OBSERV_ETAB_ID = ioe.ID
join IMPORT_OBSERV io on ioe.IMPORT_OBSERV_ID = io.ID
where SOURCE_CODE = 'UCN::16635'
order by DATE_CREATION desc
;

--
-- 7/ Lancer le script PHP de traitement des résultats d'observation :
--    un mail devrait être envoyé...
--
-- $ php public/index.php process-observed-import-results --etablissement=UCN --import-observ=RESULTAT_PASSE_A_ADMIS --source-code=UCN::16635


--
-- 8/ Vérification date de notif enregistrée.
--
select ioer.id, io.CODE, ioer.DATE_CREATION, ioer.SOURCE_CODE, ioer.RESULTAT, ioer.DATE_NOTIF, ioer.TOO_OLD
from IMPORT_OBSERV_ETAB_RESULT ioer
         join IMPORT_OBSERV_ETAB ioe on ioer.IMPORT_OBSERV_ETAB_ID = ioe.ID
         join IMPORT_OBSERV io on ioe.IMPORT_OBSERV_ID = io.ID
where SOURCE_CODE = 'UCN::16635'
order by DATE_CREATION desc
;


--
-- 9/ Restauration Vue src_these initiale.
--
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
;


--
-- 10/ Réactiver la synchro CRONée.
--
-- Ex: ssh root@host.domain.fr 'chmod +x /etc/cron.d/sygal'
