--
-- Substitutions
--

--=============================== ECOLE_DOCT ================================-

--
-- NOTE IMPORTANTE :
-- Le meilleur attribut candidat pour la détection de doublons d'ED est le "numéro national"
-- (ex : '558' pour l'ED HMPL). Ce numéro national réside dans la table des structures (colonne 'code')
-- et non dans la table des ED.
-- Par conséquent, inutile de mettre en oeuvre le mécanisme de détection/substitution de doublons au niveau des
-- ED : celui au niveau des structures suffit.
--


--
-- Vue listant les clés étrangères (FK) pointant vers 'ecole_doct'
-- dont la valeur doit être remplacée par l'id substituant éventuel.
--
-- drop view v_substit_foreign_keys_ecole_doct;
create or replace view v_substit_foreign_keys_ecole_doct as
select * from v_substit_foreign_keys
where target_table = 'ecole_doct'
  and source_table <> 'ecole_doct'
;


-- sauvegardes tables
create table ecole_doct_sav as select * from ecole_doct;


--drop view v_diff_ecole_doct; drop view src_ecole_doct;
create or replace view src_ecole_doct as
with pre as (
    SELECT NULL::text AS id,
           tmp.source_code,
           src.id AS source_id,
           s.id AS structure_id
    FROM tmp_ecole_doct tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN structure s ON s.source_code = tmp.structure_id
)
select pre.id,
       pre.source_id,
       pre.source_code,
--        coalesce(ssub.to_id, pre.structure_id) as structure_id
       pre.structure_id
from pre
where not exists ( -- on ecarte les ed substituées (i.e. ceux dont la structure liée est substituée)
    select id
    from structure_substit substit
    where substit.histo_destruction is null and pre.structure_id = substit.from_id
);
