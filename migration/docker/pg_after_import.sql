
--
-- Correction de la vue `src_role` : colonne `these_dep` => booléen
--
drop view v_diff_role
;
drop view src_role
;
create or replace view src_role
            (id, source_code, source_id, libelle, code, role_id, these_dep, structure_id,
             type_structure_dependant_id) as
SELECT NULL::text                                       AS id,
       tmp.source_code,
       src.id                                           AS source_id,
       tmp.lib_roj                                      AS libelle,
       tmp.id                                           AS code,
       (tmp.lib_roj::text || ' '::text) || s.code::text AS role_id,
       true                                             AS these_dep,
       s.id                                             AS structure_id,
       NULL::bigint                                     AS type_structure_dependant_id
FROM tmp_role tmp
         JOIN structure s ON s.source_code::text = tmp.etablissement_id::text
         JOIN etablissement e ON e.structure_id = s.id
         JOIN source src ON src.code::text = tmp.source_id::text
;


--
-- Cf. migration/docker/pg_before_import.sql
--
-- >>>> On ne peut pas créer cette MV car elle se base sur V_DIFF_INDIVIDU qui est surpprimé/générée à chaque import par unicaen/db-import
--      la solution est d'inclure le source de V_DIFF_INDIVIDU dans cette MV.
--
-- CREATE MATERIALIZED VIEW mv_indicateur_121 AS
-- select * FROM individu
-- where SOURCE_CODE in (
--     SELECT SOURCE_CODE
--     FROM V_DIFF_INDIVIDU
--     WHERE operation = 'insert'
-- )
-- ;

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
-- alter table etablissement                  alter column est_etab_inscription           type smallint;
alter table faq                            alter column ordre                          type smallint;
alter table fichier                        alter column taille                         type int;
--alter table financement                    alter column annee                          type smallint;
-- alter table information                    alter column est_visible                    type smallint;
alter table information                    alter column priorite                       type smallint;
alter table information_fichier_sav        alter column createur                       type smallint;
-- alter table notif                          alter column enabled                        type smallint;
alter table privilege                      alter column ordre                          type smallint;
alter table profil                         alter column ordre                          type smallint;
alter table profil                         alter column structure_type                 type smallint;
alter table rapport                        alter column annee_univ                     type smallint;
-- alter table role                           alter column is_default                     type smallint;
alter table soutenance_membre              alter column qualite                        type smallint;
-- alter table soutenance_membre              alter column visio                          type smallint;
-- alter table soutenance_proposition         alter column exterieur                      type smallint;
-- alter table soutenance_proposition         alter column huit_clos                      type smallint;
-- alter table soutenance_proposition         alter column label_europeen                 type smallint;
-- alter table soutenance_proposition         alter column manuscrit_anglais              type smallint;
-- alter table soutenance_proposition         alter column soutenance_anglais             type smallint;
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
