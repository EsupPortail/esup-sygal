--
-- Substitutions
--

--=============================== UNITE_RECH ================================-

--
-- NOTE IMPORTANTE :
-- Le meilleur attribut candidat pour la détection de doublons d'UR est à l'heure actuelle le "code d'UR"
-- (ex : 'UMR6634'). Ce code réside dans la table des structures (colonne 'code')
-- et non dans la table des UR.
-- Par conséquent, inutile de mettre en oeuvre le mécanisme de détection/substitution de doublons au niveau des
-- UR : celui au niveau des structures suffit.
--


--
-- Vue listant les clés étrangères (FK) pointant vers 'unite_rech'
-- dont la valeur doit être remplacée par l'id substituant éventuel.
--
-- drop view v_substit_foreign_keys_unite_rech;
create or replace view v_substit_foreign_keys_unite_rech as
select * from v_substit_foreign_keys
where target_table = 'unite_rech'
  and source_table <> 'unite_rech'
;


-- sauvegardes tables
create table unite_rech_sav as select * from unite_rech;


--drop view v_diff_unite_rech;
--drop view src_unite_rech;
create or replace view src_unite_rech as
with
SELECT NULL::text AS id,
       tmp.source_code,
       src.id AS source_id,
       s.id AS structure_id
FROM tmp_unite_rech tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN structure s ON s.source_code = tmp.structure_id;

--drop view if exists v_diff_unite_rech; drop view if exists src_unite_rech;
create or replace view src_unite_rech as
select pre.id,
       pre.source_id,
       pre.source_code,
       coalesce(ssub.to_id, pre.structure_id) as structure_id
from pre_unite_rech pre
left join structure_substit ssub on ssub.from_id = pre.structure_id and ssub.histo_destruction is null
where pre.histo_destruction is null and not exists (
    select id from structure_substit where histo_destruction is null and from_id = pre.structure_id
);
