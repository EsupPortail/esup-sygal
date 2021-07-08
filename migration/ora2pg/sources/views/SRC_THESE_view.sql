-- Generated by Ora2Pg, the Oracle database Schema converter, version 20.0
-- Copyright 2000-2019 Gilles DAROLD. All rights reserved.
-- DATASOURCE: dbi:Oracle:host=sygaldb.unicaen.fr;sid=SYGLPROD;port=1523

SET client_encoding TO 'UTF8';

\set ON_ERROR_STOP ON

CREATE OR REPLACE VIEW src_these (id, source_code, source_id, etablissement_id, doctorant_id, ecole_doct_id, unite_rech_id, titre, etat_these, resultat, lib_disc, date_prem_insc, date_prev_soutenance, date_soutenance, date_fin_confid, lib_etab_cotut, lib_pays_cotut, correc_autorisee, correc_effectuee, soutenance_autoris, date_autoris_soutenance, tem_avenant_cotut, date_abandon, date_transfert) AS SELECT 
    null                            as id,
    tmp.source_code                 as source_code,
    src.id                          as source_id,
    e.id                            as etablissement_id,
    d.id                            as doctorant_id,
    coalesce(ed_substit.id, ed.id)  as ecole_doct_id,
    coalesce(ur_substit.id, ur.id)  as unite_rech_id,
    tmp.lib_ths                     as titre,
    tmp.eta_ths                     as etat_these,
    to_number(tmp.cod_neg_tre)      as resultat,
    tmp.lib_int1_dis                as lib_disc,
    tmp.dat_deb_ths                 as date_prem_insc,
    tmp.dat_prev_sou                as date_prev_soutenance,
    tmp.dat_sou_ths                 as date_soutenance,
    tmp.dat_fin_cfd_ths             as date_fin_confid,
    tmp.lib_etab_cotut              as lib_etab_cotut,
    tmp.lib_pays_cotut              as lib_pays_cotut,
    tmp.correction_possible         as correc_autorisee,
    tmp.correction_effectuee        as correc_effectuee,
    tem_sou_aut_ths                 as soutenance_autoris,
    dat_aut_sou_ths                 as date_autoris_soutenance,
    tem_avenant_cotut               as tem_avenant_cotut,
    dat_abandon                     as date_abandon,
    dat_transfert_dep               as date_transfert
 FROM tmp_these tmp
         JOIN STRUCTURE s ON s.SOURCE_CODE = tmp.ETABLISSEMENT_ID
         join etablissement e on e.structure_id = s.id
         join source src on src.code = tmp.source_id
         join doctorant d on d.source_code = tmp.doctorant_id
         left join ecole_doct ed on ed.source_code = tmp.ecole_doct_id
         left join unite_rech ur on ur.source_code = tmp.unite_rech_id
         left join structure_substit ss_ed on ss_ed.from_structure_id = ed.structure_id
         left join ecole_doct ed_substit on ed_substit.structure_id = ss_ed.to_structure_id
         left join structure_substit ss_ur on ss_ur.from_structure_id = ur.structure_id
         left join unite_rech ur_substit on ur_substit.structure_id = ss_ur.to_structure_id;

