alter table these alter column lib_etab_cotut type varchar(100) using lib_etab_cotut::varchar(100);

drop view v_diff_these;
drop materialized view mv_indicateur_1;
drop materialized view mv_indicateur_2;
drop materialized view mv_indicateur_3;
drop materialized view mv_indicateur_4;
drop materialized view mv_indicateur_5;
drop materialized view mv_indicateur_6;
drop materialized view mv_indicateur_81;

-----

create materialized view mv_indicateur_1 as
SELECT these.id,
       these.etablissement_id,
       these.doctorant_id,
       these.ecole_doct_id,
       these.unite_rech_id,
       these.besoin_expurge,
       these.cod_unit_rech,
       these.correc_autorisee,
       these.date_autoris_soutenance,
       these.date_fin_confid,
       these.date_prem_insc,
       these.date_prev_soutenance,
       these.date_soutenance,
       these.etat_these,
       these.lib_disc,
       these.lib_etab_cotut,
       these.lib_pays_cotut,
       these.lib_unit_rech,
       these.resultat,
       these.soutenance_autoris,
       these.tem_avenant_cotut,
       these.titre,
       these.source_code,
       these.source_id,
       these.histo_createur_id,
       these.histo_creation,
       these.histo_modificateur_id,
       these.histo_modification,
       these.histo_destructeur_id,
       these.histo_destruction
FROM these these;


create materialized view mv_indicateur_2 as
SELECT t.id,
       t.etablissement_id,
       t.doctorant_id,
       t.ecole_doct_id,
       t.unite_rech_id,
       t.besoin_expurge,
       t.cod_unit_rech,
       t.correc_autorisee,
       t.date_autoris_soutenance,
       t.date_fin_confid,
       t.date_prem_insc,
       t.date_prev_soutenance,
       t.date_soutenance,
       t.etat_these,
       t.lib_disc,
       t.lib_etab_cotut,
       t.lib_pays_cotut,
       t.lib_unit_rech,
       t.resultat,
       t.soutenance_autoris,
       t.tem_avenant_cotut,
       t.titre,
       t.source_code,
       t.source_id,
       t.histo_createur_id,
       t.histo_creation,
       t.histo_modificateur_id,
       t.histo_modification,
       t.histo_destructeur_id,
       t.histo_destruction,
       t.correc_autorisee_forcee,
       t.date_abandon,
       t.date_transfert,
       t.correc_effectuee
FROM these t
         LEFT JOIN validation v ON t.id = v.these_id
         LEFT JOIN type_validation n ON v.type_validation_id = n.id
WHERE t.date_soutenance > ('now'::text::timestamp without time zone - '2 mons'::interval)
  AND t.etat_these::text = 'E'::text
  AND n.code::text = 'PAGE_DE_COUVERTURE'::text
  AND v.id IS NULL;



create materialized view mv_indicateur_3 as
SELECT t.id,
       t.etablissement_id,
       t.doctorant_id,
       t.ecole_doct_id,
       t.unite_rech_id,
       t.besoin_expurge,
       t.cod_unit_rech,
       t.correc_autorisee,
       t.date_autoris_soutenance,
       t.date_fin_confid,
       t.date_prem_insc,
       t.date_prev_soutenance,
       t.date_soutenance,
       t.etat_these,
       t.lib_disc,
       t.lib_etab_cotut,
       t.lib_pays_cotut,
       t.lib_unit_rech,
       t.resultat,
       t.soutenance_autoris,
       t.tem_avenant_cotut,
       t.titre,
       t.source_code,
       t.source_id,
       t.histo_createur_id,
       t.histo_creation,
       t.histo_modificateur_id,
       t.histo_modification,
       t.histo_destructeur_id,
       t.histo_destruction,
       t.correc_autorisee_forcee,
       t.date_abandon,
       t.date_transfert,
       t.correc_effectuee
FROM these t
         LEFT JOIN fichier_these f ON t.id = f.these_id
         LEFT JOIN fichier fi ON fi.id = f.fichier_id
         LEFT JOIN nature_fichier n ON fi.nature_id = n.id
WHERE t.date_soutenance > ('now'::text::timestamp without time zone - '1 mon'::interval)
  AND t.etat_these::text = 'E'::text
  AND n.code::text = 'THESE_PDF'::text
  AND f.id IS NULL;

create materialized view mv_indicateur_4 as
SELECT t.id,
       t.etablissement_id,
       t.doctorant_id,
       t.ecole_doct_id,
       t.unite_rech_id,
       t.besoin_expurge,
       t.cod_unit_rech,
       t.correc_autorisee,
       t.date_autoris_soutenance,
       t.date_fin_confid,
       t.date_prem_insc,
       t.date_prev_soutenance,
       t.date_soutenance,
       t.etat_these,
       t.lib_disc,
       t.lib_etab_cotut,
       t.lib_pays_cotut,
       t.lib_unit_rech,
       t.resultat,
       t.soutenance_autoris,
       t.tem_avenant_cotut,
       t.titre,
       t.source_code,
       t.source_id,
       t.histo_createur_id,
       t.histo_creation,
       t.histo_modificateur_id,
       t.histo_modification,
       t.histo_destructeur_id,
       t.histo_destruction,
       t.correc_autorisee_forcee,
       t.date_abandon,
       t.date_transfert,
       t.correc_effectuee
FROM these t
WHERE t.etat_these::text = 'E'::text
  AND t.date_soutenance < 'now'::text::timestamp without time zone;


create materialized view mv_indicateur_5 as
SELECT t.id,
       t.etablissement_id,
       t.doctorant_id,
       t.ecole_doct_id,
       t.unite_rech_id,
       t.besoin_expurge,
       t.cod_unit_rech,
       t.correc_autorisee,
       t.date_autoris_soutenance,
       t.date_fin_confid,
       t.date_prem_insc,
       t.date_prev_soutenance,
       t.date_soutenance,
       t.etat_these,
       t.lib_disc,
       t.lib_etab_cotut,
       t.lib_pays_cotut,
       t.lib_unit_rech,
       t.resultat,
       t.soutenance_autoris,
       t.tem_avenant_cotut,
       t.titre,
       t.source_code,
       t.source_id,
       t.histo_createur_id,
       t.histo_creation,
       t.histo_modificateur_id,
       t.histo_modification,
       t.histo_destructeur_id,
       t.histo_destruction,
       t.correc_autorisee_forcee,
       t.date_abandon,
       t.date_transfert,
       t.correc_effectuee
FROM these t
WHERE t.etat_these::text = 'E'::text
  AND t.date_soutenance > 'now'::text::timestamp without time zone;

create materialized view mv_indicateur_6 as
SELECT t.id,
       t.etablissement_id,
       t.doctorant_id,
       t.ecole_doct_id,
       t.unite_rech_id,
       t.besoin_expurge,
       t.cod_unit_rech,
       t.correc_autorisee,
       t.date_autoris_soutenance,
       t.date_fin_confid,
       t.date_prem_insc,
       t.date_prev_soutenance,
       t.date_soutenance,
       t.etat_these,
       t.lib_disc,
       t.lib_etab_cotut,
       t.lib_pays_cotut,
       t.lib_unit_rech,
       t.resultat,
       t.soutenance_autoris,
       t.tem_avenant_cotut,
       t.titre,
       t.source_code,
       t.source_id,
       t.histo_createur_id,
       t.histo_creation,
       t.histo_modificateur_id,
       t.histo_modification,
       t.histo_destructeur_id,
       t.histo_destruction,
       t.correc_autorisee_forcee,
       t.date_abandon,
       t.date_transfert,
       t.correc_effectuee
FROM these t
WHERE t.etat_these::text = 'E'::text
  AND t.date_prem_insc < ('now'::text::timestamp without time zone - '6 years'::interval);

create materialized view mv_indicateur_81 as
SELECT t.id,
       t.etablissement_id,
       t.doctorant_id,
       t.ecole_doct_id,
       t.unite_rech_id,
       t.besoin_expurge,
       t.cod_unit_rech,
       t.correc_autorisee,
       t.date_autoris_soutenance,
       t.date_fin_confid,
       t.date_prem_insc,
       t.date_prev_soutenance,
       t.date_soutenance,
       t.etat_these,
       t.lib_disc,
       t.lib_etab_cotut,
       t.lib_pays_cotut,
       t.lib_unit_rech,
       t.resultat,
       t.soutenance_autoris,
       t.tem_avenant_cotut,
       t.titre,
       t.source_code,
       t.source_id,
       t.histo_createur_id,
       t.histo_creation,
       t.histo_modificateur_id,
       t.histo_modification,
       t.histo_destructeur_id,
       t.histo_destruction,
       t.correc_autorisee_forcee,
       t.date_abandon,
       t.date_transfert,
       t.correc_effectuee
FROM these t
WHERE t.date_prem_insc <= ('now'::text::timestamp without time zone - '5 years'::interval)
  AND t.etat_these::text = 'E'::text
  AND (t.id IN (SELECT these_annee_univ.these_id
                FROM these_annee_univ
                         JOIN these t_1 ON these_annee_univ.these_id = t_1.id
                WHERE t_1.etat_these::text = 'E'::text
                GROUP BY these_annee_univ.these_id
                HAVING max(these_annee_univ.annee_univ)::double precision <=
                       date_part('year'::text, 'now'::text::timestamp without time zone - '1 year'::interval)));

create view v_diff_these
            (source_code, source_id, operation, u_source_id, u_etablissement_id, u_doctorant_id, u_ecole_doct_id,
             u_unite_rech_id, u_titre, u_etat_these, u_resultat, u_code_sise_disc, u_lib_disc, u_date_prem_insc,
             u_date_prev_soutenance, u_date_soutenance, u_date_fin_confid, u_lib_etab_cotut, u_lib_pays_cotut,
             u_correc_autorisee, u_correc_effectuee, u_soutenance_autoris, u_date_autoris_soutenance,
             u_tem_avenant_cotut, u_date_abandon, u_date_transfert, s_source_id, s_etablissement_id, s_doctorant_id,
             s_ecole_doct_id, s_unite_rech_id, s_titre, s_etat_these, s_resultat, s_code_sise_disc, s_lib_disc,
             s_date_prem_insc, s_date_prev_soutenance, s_date_soutenance, s_date_fin_confid, s_lib_etab_cotut,
             s_lib_pays_cotut, s_correc_autorisee, s_correc_effectuee, s_soutenance_autoris, s_date_autoris_soutenance,
             s_tem_avenant_cotut, s_date_abandon, s_date_transfert, d_source_id, d_etablissement_id, d_doctorant_id,
             d_ecole_doct_id, d_unite_rech_id, d_titre, d_etat_these, d_resultat, d_code_sise_disc, d_lib_disc,
             d_date_prem_insc, d_date_prev_soutenance, d_date_soutenance, d_date_fin_confid, d_lib_etab_cotut,
             d_lib_pays_cotut, d_correc_autorisee, d_correc_effectuee, d_soutenance_autoris, d_date_autoris_soutenance,
             d_tem_avenant_cotut, d_date_abandon, d_date_transfert)
as
WITH diff AS (
    SELECT COALESCE(s.source_code, d.source_code) AS source_code,
           COALESCE(s.source_id, d.source_id)     AS source_id,
           CASE
               WHEN s.source_code IS NOT NULL AND d.source_code IS NULL THEN 'insert'::text
               WHEN s.source_code IS NOT NULL AND d.source_code IS NOT NULL AND
                    (d.histo_destruction IS NULL OR d.histo_destruction > 'now'::text::timestamp(0) without time zone)
                   THEN 'update'::text
               WHEN s.source_code IS NOT NULL AND d.source_code IS NOT NULL AND d.histo_destruction IS NOT NULL AND
                    d.histo_destruction <= 'now'::text::timestamp(0) without time zone THEN 'undelete'::text
               WHEN s.source_code IS NULL AND d.source_code IS NOT NULL AND
                    (d.histo_destruction IS NULL OR d.histo_destruction > 'now'::text::timestamp(0) without time zone)
                   THEN 'delete'::text
               ELSE NULL::text
               END                                AS operation,
           CASE
               WHEN d.source_id <> s.source_id OR d.source_id IS NULL AND s.source_id IS NOT NULL OR
                    d.source_id IS NOT NULL AND s.source_id IS NULL THEN 1
               ELSE 0
               END                                AS u_source_id,
           CASE
               WHEN d.etablissement_id <> s.etablissement_id OR
                    d.etablissement_id IS NULL AND s.etablissement_id IS NOT NULL OR
                    d.etablissement_id IS NOT NULL AND s.etablissement_id IS NULL THEN 1
               ELSE 0
               END                                AS u_etablissement_id,
           CASE
               WHEN d.doctorant_id <> s.doctorant_id OR d.doctorant_id IS NULL AND s.doctorant_id IS NOT NULL OR
                    d.doctorant_id IS NOT NULL AND s.doctorant_id IS NULL THEN 1
               ELSE 0
               END                                AS u_doctorant_id,
           CASE
               WHEN d.ecole_doct_id <> s.ecole_doct_id OR d.ecole_doct_id IS NULL AND s.ecole_doct_id IS NOT NULL OR
                    d.ecole_doct_id IS NOT NULL AND s.ecole_doct_id IS NULL THEN 1
               ELSE 0
               END                                AS u_ecole_doct_id,
           CASE
               WHEN d.unite_rech_id <> s.unite_rech_id OR d.unite_rech_id IS NULL AND s.unite_rech_id IS NOT NULL OR
                    d.unite_rech_id IS NOT NULL AND s.unite_rech_id IS NULL THEN 1
               ELSE 0
               END                                AS u_unite_rech_id,
           CASE
               WHEN d.titre::text <> s.titre::text OR d.titre IS NULL AND s.titre IS NOT NULL OR
                    d.titre IS NOT NULL AND s.titre IS NULL THEN 1
               ELSE 0
               END                                AS u_titre,
           CASE
               WHEN d.etat_these::text <> s.etat_these::text OR d.etat_these IS NULL AND s.etat_these IS NOT NULL OR
                    d.etat_these IS NOT NULL AND s.etat_these IS NULL THEN 1
               ELSE 0
               END                                AS u_etat_these,
           CASE
               WHEN d.resultat::numeric <> s.resultat OR d.resultat IS NULL AND s.resultat IS NOT NULL OR
                    d.resultat IS NOT NULL AND s.resultat IS NULL THEN 1
               ELSE 0
               END                                AS u_resultat,
           CASE
               WHEN d.code_sise_disc::text <> s.code_sise_disc::text OR
                    d.code_sise_disc IS NULL AND s.code_sise_disc IS NOT NULL OR
                    d.code_sise_disc IS NOT NULL AND s.code_sise_disc IS NULL THEN 1
               ELSE 0
               END                                AS u_code_sise_disc,
           CASE
               WHEN d.lib_disc::text <> s.lib_disc::text OR d.lib_disc IS NULL AND s.lib_disc IS NOT NULL OR
                    d.lib_disc IS NOT NULL AND s.lib_disc IS NULL THEN 1
               ELSE 0
               END                                AS u_lib_disc,
           CASE
               WHEN d.date_prem_insc <> s.date_prem_insc OR d.date_prem_insc IS NULL AND s.date_prem_insc IS NOT NULL OR
                    d.date_prem_insc IS NOT NULL AND s.date_prem_insc IS NULL THEN 1
               ELSE 0
               END                                AS u_date_prem_insc,
           CASE
               WHEN d.date_prev_soutenance <> s.date_prev_soutenance OR
                    d.date_prev_soutenance IS NULL AND s.date_prev_soutenance IS NOT NULL OR
                    d.date_prev_soutenance IS NOT NULL AND s.date_prev_soutenance IS NULL THEN 1
               ELSE 0
               END                                AS u_date_prev_soutenance,
           CASE
               WHEN d.date_soutenance <> s.date_soutenance OR
                    d.date_soutenance IS NULL AND s.date_soutenance IS NOT NULL OR
                    d.date_soutenance IS NOT NULL AND s.date_soutenance IS NULL THEN 1
               ELSE 0
               END                                AS u_date_soutenance,
           CASE
               WHEN d.date_fin_confid <> s.date_fin_confid OR
                    d.date_fin_confid IS NULL AND s.date_fin_confid IS NOT NULL OR
                    d.date_fin_confid IS NOT NULL AND s.date_fin_confid IS NULL THEN 1
               ELSE 0
               END                                AS u_date_fin_confid,
           CASE
               WHEN d.lib_etab_cotut::text <> s.lib_etab_cotut::text OR
                    d.lib_etab_cotut IS NULL AND s.lib_etab_cotut IS NOT NULL OR
                    d.lib_etab_cotut IS NOT NULL AND s.lib_etab_cotut IS NULL THEN 1
               ELSE 0
               END                                AS u_lib_etab_cotut,
           CASE
               WHEN d.lib_pays_cotut::text <> s.lib_pays_cotut::text OR
                    d.lib_pays_cotut IS NULL AND s.lib_pays_cotut IS NOT NULL OR
                    d.lib_pays_cotut IS NOT NULL AND s.lib_pays_cotut IS NULL THEN 1
               ELSE 0
               END                                AS u_lib_pays_cotut,
           CASE
               WHEN d.correc_autorisee::text <> s.correc_autorisee::text OR
                    d.correc_autorisee IS NULL AND s.correc_autorisee IS NOT NULL OR
                    d.correc_autorisee IS NOT NULL AND s.correc_autorisee IS NULL THEN 1
               ELSE 0
               END                                AS u_correc_autorisee,
           CASE
               WHEN d.correc_effectuee::text <> s.correc_effectuee::text OR
                    d.correc_effectuee IS NULL AND s.correc_effectuee IS NOT NULL OR
                    d.correc_effectuee IS NOT NULL AND s.correc_effectuee IS NULL THEN 1
               ELSE 0
               END                                AS u_correc_effectuee,
           CASE
               WHEN d.soutenance_autoris::text <> s.soutenance_autoris::text OR
                    d.soutenance_autoris IS NULL AND s.soutenance_autoris IS NOT NULL OR
                    d.soutenance_autoris IS NOT NULL AND s.soutenance_autoris IS NULL THEN 1
               ELSE 0
               END                                AS u_soutenance_autoris,
           CASE
               WHEN d.date_autoris_soutenance <> s.date_autoris_soutenance OR
                    d.date_autoris_soutenance IS NULL AND s.date_autoris_soutenance IS NOT NULL OR
                    d.date_autoris_soutenance IS NOT NULL AND s.date_autoris_soutenance IS NULL THEN 1
               ELSE 0
               END                                AS u_date_autoris_soutenance,
           CASE
               WHEN d.tem_avenant_cotut::text <> s.tem_avenant_cotut::text OR
                    d.tem_avenant_cotut IS NULL AND s.tem_avenant_cotut IS NOT NULL OR
                    d.tem_avenant_cotut IS NOT NULL AND s.tem_avenant_cotut IS NULL THEN 1
               ELSE 0
               END                                AS u_tem_avenant_cotut,
           CASE
               WHEN d.date_abandon <> s.date_abandon OR d.date_abandon IS NULL AND s.date_abandon IS NOT NULL OR
                    d.date_abandon IS NOT NULL AND s.date_abandon IS NULL THEN 1
               ELSE 0
               END                                AS u_date_abandon,
           CASE
               WHEN d.date_transfert <> s.date_transfert OR d.date_transfert IS NULL AND s.date_transfert IS NOT NULL OR
                    d.date_transfert IS NOT NULL AND s.date_transfert IS NULL THEN 1
               ELSE 0
               END                                AS u_date_transfert,
           s.source_id                            AS s_source_id,
           s.etablissement_id                     AS s_etablissement_id,
           s.doctorant_id                         AS s_doctorant_id,
           s.ecole_doct_id                        AS s_ecole_doct_id,
           s.unite_rech_id                        AS s_unite_rech_id,
           s.titre                                AS s_titre,
           s.etat_these                           AS s_etat_these,
           s.resultat                             AS s_resultat,
           s.code_sise_disc                       AS s_code_sise_disc,
           s.lib_disc                             AS s_lib_disc,
           s.date_prem_insc                       AS s_date_prem_insc,
           s.date_prev_soutenance                 AS s_date_prev_soutenance,
           s.date_soutenance                      AS s_date_soutenance,
           s.date_fin_confid                      AS s_date_fin_confid,
           s.lib_etab_cotut                       AS s_lib_etab_cotut,
           s.lib_pays_cotut                       AS s_lib_pays_cotut,
           s.correc_autorisee                     AS s_correc_autorisee,
           s.correc_effectuee                     AS s_correc_effectuee,
           s.soutenance_autoris                   AS s_soutenance_autoris,
           s.date_autoris_soutenance              AS s_date_autoris_soutenance,
           s.tem_avenant_cotut                    AS s_tem_avenant_cotut,
           s.date_abandon                         AS s_date_abandon,
           s.date_transfert                       AS s_date_transfert,
           d.source_id                            AS d_source_id,
           d.etablissement_id                     AS d_etablissement_id,
           d.doctorant_id                         AS d_doctorant_id,
           d.ecole_doct_id                        AS d_ecole_doct_id,
           d.unite_rech_id                        AS d_unite_rech_id,
           d.titre                                AS d_titre,
           d.etat_these                           AS d_etat_these,
           d.resultat                             AS d_resultat,
           d.code_sise_disc                       AS d_code_sise_disc,
           d.lib_disc                             AS d_lib_disc,
           d.date_prem_insc                       AS d_date_prem_insc,
           d.date_prev_soutenance                 AS d_date_prev_soutenance,
           d.date_soutenance                      AS d_date_soutenance,
           d.date_fin_confid                      AS d_date_fin_confid,
           d.lib_etab_cotut                       AS d_lib_etab_cotut,
           d.lib_pays_cotut                       AS d_lib_pays_cotut,
           d.correc_autorisee                     AS d_correc_autorisee,
           d.correc_effectuee                     AS d_correc_effectuee,
           d.soutenance_autoris                   AS d_soutenance_autoris,
           d.date_autoris_soutenance              AS d_date_autoris_soutenance,
           d.tem_avenant_cotut                    AS d_tem_avenant_cotut,
           d.date_abandon                         AS d_date_abandon,
           d.date_transfert                       AS d_date_transfert
    FROM these d
             JOIN source src ON src.id = d.source_id AND src.importable = true
             FULL JOIN src_these s ON s.source_id = d.source_id AND s.source_code::text = d.source_code::text
)
SELECT diff.source_code,
       diff.source_id,
       diff.operation,
       diff.u_source_id,
       diff.u_etablissement_id,
       diff.u_doctorant_id,
       diff.u_ecole_doct_id,
       diff.u_unite_rech_id,
       diff.u_titre,
       diff.u_etat_these,
       diff.u_resultat,
       diff.u_code_sise_disc,
       diff.u_lib_disc,
       diff.u_date_prem_insc,
       diff.u_date_prev_soutenance,
       diff.u_date_soutenance,
       diff.u_date_fin_confid,
       diff.u_lib_etab_cotut,
       diff.u_lib_pays_cotut,
       diff.u_correc_autorisee,
       diff.u_correc_effectuee,
       diff.u_soutenance_autoris,
       diff.u_date_autoris_soutenance,
       diff.u_tem_avenant_cotut,
       diff.u_date_abandon,
       diff.u_date_transfert,
       diff.s_source_id,
       diff.s_etablissement_id,
       diff.s_doctorant_id,
       diff.s_ecole_doct_id,
       diff.s_unite_rech_id,
       diff.s_titre,
       diff.s_etat_these,
       diff.s_resultat,
       diff.s_code_sise_disc,
       diff.s_lib_disc,
       diff.s_date_prem_insc,
       diff.s_date_prev_soutenance,
       diff.s_date_soutenance,
       diff.s_date_fin_confid,
       diff.s_lib_etab_cotut,
       diff.s_lib_pays_cotut,
       diff.s_correc_autorisee,
       diff.s_correc_effectuee,
       diff.s_soutenance_autoris,
       diff.s_date_autoris_soutenance,
       diff.s_tem_avenant_cotut,
       diff.s_date_abandon,
       diff.s_date_transfert,
       diff.d_source_id,
       diff.d_etablissement_id,
       diff.d_doctorant_id,
       diff.d_ecole_doct_id,
       diff.d_unite_rech_id,
       diff.d_titre,
       diff.d_etat_these,
       diff.d_resultat,
       diff.d_code_sise_disc,
       diff.d_lib_disc,
       diff.d_date_prem_insc,
       diff.d_date_prev_soutenance,
       diff.d_date_soutenance,
       diff.d_date_fin_confid,
       diff.d_lib_etab_cotut,
       diff.d_lib_pays_cotut,
       diff.d_correc_autorisee,
       diff.d_correc_effectuee,
       diff.d_soutenance_autoris,
       diff.d_date_autoris_soutenance,
       diff.d_tem_avenant_cotut,
       diff.d_date_abandon,
       diff.d_date_transfert
FROM diff
WHERE diff.operation IS NOT NULL
  AND (diff.operation = 'undelete'::text OR 0 < (diff.u_source_id + diff.u_etablissement_id + diff.u_doctorant_id +
                                                 diff.u_ecole_doct_id + diff.u_unite_rech_id + diff.u_titre +
                                                 diff.u_etat_these + diff.u_resultat + diff.u_code_sise_disc +
                                                 diff.u_lib_disc + diff.u_date_prem_insc + diff.u_date_prev_soutenance +
                                                 diff.u_date_soutenance + diff.u_date_fin_confid +
                                                 diff.u_lib_etab_cotut + diff.u_lib_pays_cotut +
                                                 diff.u_correc_autorisee + diff.u_correc_effectuee +
                                                 diff.u_soutenance_autoris + diff.u_date_autoris_soutenance +
                                                 diff.u_tem_avenant_cotut + diff.u_date_abandon +
                                                 diff.u_date_transfert));

