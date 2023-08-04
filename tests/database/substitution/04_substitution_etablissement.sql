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
-- On utilise tout de même une table PRE_ETABLISSEMENT et la vue associée SRC_PRE_ETABLISSEMENT.
--

-- sauvegardes tables
create table etablissement_sav as select * from etablissement;

-- nouvelle table PRE_ETABLISSEMENT
create table pre_etablissement (like etablissement including all);
insert into pre_etablissement select * from etablissement;
alter table pre_etablissement drop column est_membre;
alter table pre_etablissement drop column est_associe;
alter table pre_etablissement drop column est_comue;
alter table pre_etablissement drop column est_etab_inscription;
alter table pre_etablissement drop column signature_convocation_id;
alter table pre_etablissement drop column email_assistance;
alter table pre_etablissement drop column email_bibliotheque;
alter table pre_etablissement drop column email_doctorat;
alter table pre_etablissement drop column est_ced;
alter table pre_etablissement add constraint pre_etablissement_source_fk foreign key (source_id) references source on delete cascade;
alter table pre_etablissement add constraint pre_etablissement_hc_fk foreign key (histo_createur_id) references utilisateur on delete cascade;
alter table pre_etablissement add constraint pre_etablissement_hm_fk foreign key (histo_modificateur_id) references utilisateur on delete cascade;
alter table pre_etablissement add constraint pre_etablissement_hd_fk foreign key (histo_destructeur_id) references utilisateur on delete cascade;
alter table pre_etablissement add constraint pre_etablissement_structure_fk foreign key (structure_id) references pre_structure on delete cascade;
create sequence if not exists pre_etablissement_id_seq owned by pre_etablissement.id;
select setval('pre_etablissement_id_seq', (select max(id) from pre_etablissement));

--drop view v_diff_pre_etablissement;
--drop view src_pre_etablissement;
create or replace view src_pre_etablissement as
SELECT NULL::text AS id,
       tmp.source_code,
       src.id AS source_id,
       s.id AS structure_id
FROM tmp_etablissement tmp
         JOIN source src ON src.id = tmp.source_id
         JOIN structure s ON s.source_code = tmp.structure_id;

drop view if exists v_diff_etablissement;
drop view if exists src_etablissement;
create or replace view src_etablissement as
select pre.id,
       coalesce(ssub.to_id, pre.structure_id) as structure_id,
       pre.domaine,
       pre.source_id,
       pre.source_code
from pre_etablissement pre
left join structure_substit ssub on ssub.from_id = pre.structure_id and ssub.histo_destruction is null
where pre.histo_destruction is null and not exists (
    select * from structure_substit where histo_destruction is null and from_id = pre.structure_id
);


--
-- Liste des etablissements substitués
--
select ss.histo_destruction, ss.npd, ss.from_id, pe.id from_etab_id, ss.to_id, s.id to_etab_id, ps.npd_force, ps.libelle, s.libelle
    from structure_substit ss
        join pre_structure ps on ss.from_id = ps.id
        join pre_etablissement pe on ps.id = pe.structure_id
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
