
--
-- Cf. migration/docker/pg_before_import.sql
--
CREATE MATERIALIZED VIEW mv_indicateur_121 AS
select * FROM individu
where SOURCE_CODE in (
    SELECT SOURCE_CODE
    FROM V_DIFF_INDIVIDU
    WHERE operation = 'insert'
)
;

--
-- Peuplement des VM.
--
refresh materialized view mv_recherche_these;
refresh materialized view mv_indicateur_1;
refresh materialized view mv_indicateur_2;
refresh materialized view mv_indicateur_3;
refresh materialized view mv_indicateur_4;
refresh materialized view mv_indicateur_5;
refresh materialized view mv_indicateur_6;
refresh materialized view mv_indicateur_7;
refresh materialized view mv_indicateur_21;
refresh materialized view mv_indicateur_61;
refresh materialized view mv_indicateur_62;
refresh materialized view mv_indicateur_81;
refresh materialized view mv_indicateur_101;
refresh materialized view mv_indicateur_121;
refresh materialized view mv_indicateur_141;


--
-- Transformation de certains `bigint` inutiles en `int` ou `smallint` quand c'est possible
-- (peut-être faisable en amont dans la config ora2pg ?)
--
alter table categorie_privilege            alter column ordre                          type smallint;
alter table etablissement                  alter column est_etab_inscription           type smallint;
alter table faq                            alter column ordre                          type smallint;
alter table fichier                        alter column taille                         type int;
--alter table financement                    alter column annee                          type smallint;
alter table information                    alter column est_visible                    type smallint;
alter table information                    alter column priorite                       type smallint;
alter table information_fichier_sav        alter column createur                       type smallint;
alter table notif                          alter column enabled                        type smallint;
alter table privilege                      alter column ordre                          type smallint;
alter table profil                         alter column ordre                          type smallint;
alter table profil                         alter column structure_type                 type smallint;
alter table rapport_annuel                 alter column annee_univ                     type smallint;
alter table role                           alter column is_default                     type smallint;
alter table soutenance_membre              alter column qualite                        type smallint;
alter table soutenance_membre              alter column visio                          type smallint;
alter table soutenance_proposition         alter column exterieur                      type smallint;
alter table soutenance_proposition         alter column huit_clos                      type smallint;
alter table soutenance_proposition         alter column label_europeen                 type smallint;
alter table soutenance_proposition         alter column manuscrit_anglais              type smallint;
alter table soutenance_proposition         alter column soutenance_anglais             type smallint;
--alter table these_annee_univ               alter column annee_univ                     type smallint;
--alter table tmp_these                      alter column annee_univ_1ere_insc           type smallint;
--alter table tmp_these_annee_univ           alter column annee_univ                     type smallint;
alter table utilisateur                    alter column state                          type smallint;
alter table wf_etape                       alter column chemin                         type smallint;
--alter table wf_etape                       alter column ordre                          type smallint;
/******* alter générés avec
with tmp as (
select c.*
from information_schema.columns c
left join information_schema.views v on c.table_name = v.table_name
where v.table_name is null
and c.table_catalog = 'sygal_dev'
and c.table_schema = 'public'
and c.data_type = 'bigint'
and c.column_name not like '%id'
)
select 'alter table '||rpad(table_name, 30)||' alter column '||rpad(column_name, 30)||' type smallint;'
from tmp
order by table_name, column_name;
*********/


--
-- Corrections de vues
--
create or replace view src_these
            (id, source_code, source_id, etablissement_id, doctorant_id, ecole_doct_id, unite_rech_id, titre,
             etat_these, resultat, lib_disc, date_prem_insc, date_prev_soutenance, date_soutenance, date_fin_confid,
             lib_etab_cotut, lib_pays_cotut, correc_autorisee, correc_effectuee, soutenance_autoris,
             date_autoris_soutenance, tem_avenant_cotut, date_abandon, date_transfert)
as
SELECT NULL::text                                                                  AS id,
       tmp.source_code,
       src.id                                                                      AS source_id,
       e.id                                                                        AS etablissement_id,
       d.id                                                                        AS doctorant_id,
       COALESCE(ed_substit.id, ed.id)                                              AS ecole_doct_id,
       COALESCE(ur_substit.id, ur.id)                                              AS unite_rech_id,
       tmp.lib_ths                                                                 AS titre,
       tmp.eta_ths                                                                 AS etat_these,
       case tmp.cod_neg_tre when '0' then 0 when '1' then 1 else null::numeric end AS resultat,
       tmp.lib_int1_dis                                                            AS lib_disc,
       tmp.dat_deb_ths                                                             AS date_prem_insc,
       tmp.dat_prev_sou                                                            AS date_prev_soutenance,
       tmp.dat_sou_ths                                                             AS date_soutenance,
       tmp.dat_fin_cfd_ths                                                         AS date_fin_confid,
       tmp.lib_etab_cotut,
       tmp.lib_pays_cotut,
       tmp.correction_possible                                                     AS correc_autorisee,
       tmp.correction_effectuee                                                    AS correc_effectuee,
       tmp.tem_sou_aut_ths                                                         AS soutenance_autoris,
       tmp.dat_aut_sou_ths                                                         AS date_autoris_soutenance,
       tmp.tem_avenant_cotut,
       tmp.dat_abandon                                                             AS date_abandon,
       tmp.dat_transfert_dep                                                       AS date_transfert
FROM tmp_these tmp
         JOIN structure s ON s.source_code::text = tmp.etablissement_id::text
         JOIN etablissement e ON e.structure_id = s.id
         JOIN source src ON src.code::text = tmp.source_id::text
         JOIN doctorant d ON d.source_code::text = tmp.doctorant_id::text
         LEFT JOIN ecole_doct ed ON ed.source_code::text = tmp.ecole_doct_id::text
         LEFT JOIN unite_rech ur ON ur.source_code::text = tmp.unite_rech_id::text
         LEFT JOIN structure_substit ss_ed ON ss_ed.from_structure_id = ed.structure_id
         LEFT JOIN ecole_doct ed_substit ON ed_substit.structure_id = ss_ed.to_structure_id
         LEFT JOIN structure_substit ss_ur ON ss_ur.from_structure_id = ur.structure_id
         LEFT JOIN unite_rech ur_substit ON ur_substit.structure_id = ss_ur.to_structure_id;

