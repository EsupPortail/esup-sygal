--
-- patch : modif pas encore faite en prod
--

create sequence if not exists source_id_seq;
select setval('source_id_seq', coalesce(max(id),1)) from source;
alter table source alter column id set default nextval('source_id_seq');

alter table tmp_doctorant add column code_apprenant_in_source varchar(128);
alter table doctorant add column code_apprenant_in_source varchar(128);

---

alter table source add synchro_insert_enabled boolean default true not null;
alter table source add synchro_update_enabled boolean default true not null;
alter table source add synchro_undelete_enabled boolean default true not null;
alter table source add synchro_delete_enabled boolean default true not null;
comment on column source.synchro_insert_enabled is 'Indique si dans le cadre d''une synchro l''opération ''insert'' est autorisée.';
comment on column source.synchro_update_enabled is 'Indique si dans le cadre d''une synchro l''opération ''update'' est autorisée.';
comment on column source.synchro_undelete_enabled is 'Indique si dans le cadre d''une synchro l''opération ''undelete'' est autorisée.';
comment on column source.synchro_delete_enabled is 'Indique si dans le cadre d''une synchro l''opération ''delete'' est autorisée.';

/*
drop view v_diff_pre_structure;
drop view v_diff_structure;
drop view v_diff_pre_etablissement;
drop view v_diff_etablissement;
drop view v_diff_pre_ecole_doct;
drop view v_diff_ecole_doct;
drop view v_diff_pre_unite_rech;
drop view v_diff_unite_rech;
drop view v_diff_pre_individu;
drop view v_diff_individu;
drop view v_diff_pre_doctorant;
drop view v_diff_doctorant;
drop view v_diff_pre_doctorant;
drop view v_diff_doctorant;
drop view v_diff_these_annee_univ;
drop view v_diff_origine_financement;
drop view v_diff_financement;
drop view v_diff_titre_acces;
alter table source drop synchro_insert_enabled ;
*/
