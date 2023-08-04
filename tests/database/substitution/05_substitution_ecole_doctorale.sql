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
-- On utilise tout de même une table PRE_ECOLE_DOCT et la vue associée SRC_PRE_ECOLE_DOCT.
--

-- sauvegardes tables
create table ecole_doct_sav as select * from ecole_doct;

-- nouvelle table PRE_ECOLE_DOCT
create table pre_ecole_doct (like ecole_doct including all);
insert into pre_ecole_doct select * from ecole_doct;
alter table pre_ecole_doct add constraint pre_ecole_doct_source_fk foreign key (source_id) references source on delete cascade;
alter table pre_ecole_doct add constraint pre_ecole_doct_hc_fk foreign key (histo_createur_id) references utilisateur on delete cascade;
alter table pre_ecole_doct add constraint pre_ecole_doct_hm_fk foreign key (histo_modificateur_id) references utilisateur on delete cascade;
alter table pre_ecole_doct add constraint pre_ecole_doct_hd_fk foreign key (histo_destructeur_id) references utilisateur on delete cascade;
alter table pre_ecole_doct add constraint pre_ecole_doct_structure_fk foreign key (structure_id) references pre_structure on delete cascade;
create sequence if not exists pre_ecole_doct_id_seq owned by pre_ecole_doct.id;
select setval('pre_ecole_doct_id_seq', (select max(id) from pre_ecole_doct));

--drop view v_diff_pre_ecole_doct;
--drop view src_pre_ecole_doct;
create or replace view src_pre_ecole_doct as
SELECT NULL::text AS id,
       tmp.source_code,
       src.id AS source_id,
       s.id AS structure_id
FROM tmp_ecole_doct tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN structure s ON s.source_code = tmp.structure_id;

drop view if exists v_diff_ecole_doct;
drop view if exists src_ecole_doct;
create or replace view src_ecole_doct as
select pre.id,
       coalesce(ssub.to_id, pre.structure_id) as structure_id,
       pre.source_id,
       pre.source_code
from pre_ecole_doct pre
left join structure_substit ssub on ssub.from_id = pre.structure_id and ssub.histo_destruction is null
-- left join ecole_doct_substit sub on sub.from_id = pre.id and sub.histo_destruction is null
where pre.histo_destruction is null and not exists (
    select * from structure_substit where histo_destruction is null and from_id = pre.structure_id
);
