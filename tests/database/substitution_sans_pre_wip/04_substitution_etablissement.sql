--
-- Substitutions
--

--=============================== ETABLISSEMENT ================================-

--
-- NOTE IMPORTANTE :
-- Le meilleur attribut candidat pour la détection de doublons d'établissements est le "code établissement"
-- (ex : '0132133Y' pour AIX-MARSEILLE UNIVERSITE). Ce code réside dans la table des structures (colonne 'code')
-- et non dans la table des établissements.
-- Par conséquent, inutile de mettre en oeuvre le mécanisme de détection/substitution de doublons au niveau des
-- établissements : celui au niveau des structures suffit.
--


--
-- Vue listant les clés étrangères (FK) pointant vers 'etablissement'
-- dont la valeur doit être remplacée par l'id substituant éventuel.
--
-- drop view v_substit_foreign_keys_etablissement;
create or replace view v_substit_foreign_keys_etablissement as
select * from v_substit_foreign_keys
where target_table = 'etablissement'
  and source_table <> 'etablissement'
;


-- sauvegardes tables
create table etablissement_sav as select * from etablissement;


--drop view v_diff_etablissement;
--drop view src_etablissement;
create or replace view src_etablissement as
with pre as (
    SELECT NULL::text AS id,
           tmp.source_code,
           src.id AS source_id,
           s.id AS structure_id
    FROM tmp_etablissement tmp
             JOIN source src ON src.id = tmp.source_id
             JOIN structure s ON s.source_code = tmp.structure_id
)
select pre.id,
       pre.source_id,
       pre.source_code,
--        coalesce(ssub.to_id, pre.structure_id) as structure_id,
       pre.structure_id,
       pre.domaine
from pre
where not exists ( -- on ecarte les etablissements substitués (i.e. ceux dont la structure liée est substituée)
    select id
    from structure_substit substit
    where substit.histo_destruction is null and pre.structure_id = substit.from_id
);


--
-- Liste des etablissements substitués
--
select ss.histo_destruction, ss.npd, ss.from_id, pe.id from_etab_id, ss.to_id, s.id to_etab_id, ps.npd_force, ps.libelle, s.libelle
    from structure_substit ss
        join structure ps on ss.from_id = ps.id
        join etablissement pe on ps.id = pe.structure_id
        join structure s on ss.to_id = s.id and s.histo_destruction is null
        join etablissement e on s.id = e.structure_id and e.histo_destruction is null
    order by to_id, from_id;

select * from structure_substit where from_id=9738;

with c as (select structure_id, count(*) from etablissement group by structure_id having count(*) > 1)
select * from etablissement e
         join structure s on e.structure_id = s.id --and s.source_id <> 1
--          join structure_substit ss on e.structure_id = ss.from_id
         where structure_id in (select structure_id from c)
         order by structure_id;
