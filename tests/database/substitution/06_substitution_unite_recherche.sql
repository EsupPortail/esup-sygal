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
-- On utilise tout de même une table PRE_UNITE_RECH et la vue associée SRC_PRE_UNITE_RECH.
--

-- sauvegardes tables
create table unite_rech_sav as select * from unite_rech;

-- nouvelle table PRE_UNITE_RECH
create table pre_unite_rech (like unite_rech including all);
insert into pre_unite_rech select * from unite_rech;
alter table pre_unite_rech add constraint pre_unite_rech_source_fk foreign key (source_id) references source on delete cascade;
alter table pre_unite_rech add constraint pre_unite_rech_hc_fk foreign key (histo_createur_id) references utilisateur on delete cascade;
alter table pre_unite_rech add constraint pre_unite_rech_hm_fk foreign key (histo_modificateur_id) references utilisateur on delete cascade;
alter table pre_unite_rech add constraint pre_unite_rech_hd_fk foreign key (histo_destructeur_id) references utilisateur on delete cascade;
alter table pre_unite_rech add constraint pre_unite_rech_structure_fk foreign key (structure_id) references pre_structure on delete cascade;
create sequence if not exists pre_unite_rech_id_seq owned by pre_unite_rech.id;
select setval('pre_unite_rech_id_seq', (select max(id) from pre_unite_rech));

-- Pseudo-substitution d'URs sur la base de leur structure liée (pre_unite_rech.structure_id)
--drop view unite_rech_substit;
create or replace view unite_rech_substit as
select pe.id as from_id,
       e.id as to_id,
       substit_npd_structure(ps) npd,
       null as histo_destruction
from pre_unite_rech pe
         join pre_structure ps on ps.id = pe.structure_id
         join structure_substit sub on sub.from_id = ps.id and sub.histo_destruction is null
         join unite_rech e on sub.to_id = e.structure_id;

--drop view v_diff_pre_unite_rech;
--drop view src_pre_unite_rech;
create or replace view src_pre_unite_rech as
SELECT NULL::text AS id,
       tmp.source_code,
       src.id AS source_id,
       s.id AS structure_id
FROM tmp_unite_rech tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN structure s ON s.source_code = tmp.structure_id;

drop view if exists v_diff_unite_rech;
drop view if exists src_unite_rech;
create or replace view src_unite_rech as
select pre.id,
       coalesce(ssub.to_id, pre.structure_id) as structure_id,
       pre.source_id,
       pre.source_code
from pre_unite_rech pre
left join structure_substit ssub on ssub.from_id = pre.structure_id and ssub.histo_destruction is null
where pre.histo_destruction is null and not exists (
    select * from structure_substit where histo_destruction is null and from_id = pre.structure_id
);
