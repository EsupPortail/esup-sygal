--
-- 9.2.0
--

--
-- Erreur dans la vue src_these.
--

drop view if exists v_diff_these;
drop view src_these;
create or replace view src_these
            (id, source_code, source_id, doctorant_id, etablissement_id, ecole_doct_id, unite_rech_id, titre,
             etat_these, resultat, discipline_sise_code, lib_disc, date_prem_insc, date_soutenance, date_fin_confid,
             lib_etab_cotut, lib_pays_cotut, correc_autorisee, correc_effectuee, soutenance_autoris, tem_avenant_cotut,
             date_abandon, date_transfert)
as
WITH pre AS (WITH version_corrigee_deposee AS (SELECT t.source_code,
                                                      f.id AS fichier_id
                                               FROM fichier_these ft
                                                        JOIN these t ON ft.these_id = t.id
                                                        JOIN fichier f ON ft.fichier_id = f.id AND f.histo_destruction IS NULL
                                                        JOIN nature_fichier nf
                                                             ON f.nature_id = nf.id AND nf.code::text = 'THESE_PDF'::text
                                                        JOIN version_fichier vf
                                                             ON f.version_fichier_id = vf.id AND vf.code::text = 'VOC'::text
                                               WHERE ft.est_annexe = false
                                                 AND ft.est_expurge = false
                                                 AND ft.retraitement IS NULL)
             SELECT NULL::bigint                   AS id,
                    tmp.source_code,
                    src.id                         AS source_id,
                    e.id                           AS etablissement_id,
                    d.id                           AS doctorant_id,
                    ed.id                          AS ecole_doct_id,
                    ur.id                          AS unite_rech_id,
                    tmp.lib_ths                    AS titre,
                    tmp.eta_ths                    AS etat_these,
                    tmp.cod_neg_tre::numeric       AS resultat,
                    tmp.code_sise_disc             AS discipline_sise_code,
                    tmp.lib_int1_dis               AS lib_disc,
                    tmp.dat_deb_ths                AS date_prem_insc,
                    tmp.dat_sou_ths                AS date_soutenance,
                    tmp.dat_fin_cfd_ths            AS date_fin_confid,
                    tmp.lib_etab_cotut,
                    tmp.lib_pays_cotut,
                    tmp.correction_possible        AS correc_autorisee,
                    CASE
                        WHEN vcd.source_code IS NOT NULL THEN 'O'::character varying
                        ELSE tmp.correction_effectuee
                        END::character varying(30) AS correc_effectuee,
                    tmp.tem_sou_aut_ths            AS soutenance_autoris,
                    tmp.tem_avenant_cotut,
                    tmp.dat_abandon                AS date_abandon,
                    tmp.dat_transfert_dep          AS date_transfert
             FROM tmp_these tmp
                      JOIN source src ON src.id = tmp.source_id
                      JOIN etablissement e ON e.id = src.etablissement_id
                      JOIN doctorant d ON d.source_code::text = tmp.doctorant_id::text
                      LEFT JOIN ecole_doct ed ON ed.source_code::text = tmp.ecole_doct_id::text
                      LEFT JOIN unite_rech ur ON ur.source_code::text = tmp.unite_rech_id::text
                      LEFT JOIN version_corrigee_deposee vcd ON vcd.source_code::text = tmp.source_code::text)
SELECT pre.id,
       pre.source_code,
       pre.source_id,
       COALESCE(dsub.to_id, pre.doctorant_id)     AS doctorant_id,
       COALESCE(esub.to_id, pre.etablissement_id) AS etablissement_id,
       COALESCE(edsub.to_id, pre.ecole_doct_id)   AS ecole_doct_id,
       COALESCE(ursub.to_id, pre.unite_rech_id)   AS unite_rech_id,
       pre.titre,
       pre.etat_these,
       pre.resultat,
       pre.discipline_sise_code,
       pre.lib_disc,
       pre.date_prem_insc,
       pre.date_soutenance,
       pre.date_fin_confid,
       pre.lib_etab_cotut,
       pre.lib_pays_cotut,
       pre.correc_autorisee,
       pre.correc_effectuee,
       pre.soutenance_autoris,
       pre.tem_avenant_cotut,
       pre.date_abandon,
       pre.date_transfert
FROM pre
         LEFT JOIN substit_doctorant dsub ON dsub.from_id = pre.doctorant_id
         LEFT JOIN substit_etablissement esub ON esub.from_id = pre.etablissement_id
         LEFT JOIN substit_ecole_doct edsub ON edsub.from_id = pre.ecole_doct_id
         LEFT JOIN substit_unite_rech ursub ON ursub.from_id = pre.unite_rech_id;