-- ------------------------------------------------------------------------------------------------------------------
-- Tests : structure
-- ------------------------------------------------------------------------------------------------------------------

select test_substit_structure__fetches_data_for_substituant();
select test_substit_structure__creates_substit_2_doublons();
select test_substit_structure__creates_substit_3_doublons();
select test_substit_structure__removes_from_substit_si_historise();
select test_substit_structure__adds_to_substit_si_dehistorise();
select test_substit_structure__removes_from_substit_si_source_app();
select test_substit_structure__removes_from_substit_si_plus_source_app();
select test_substit_structure__adds_to_substit_si_npd_force();
select test_substit_structure__updates_substits_si_modif_code();
select test_substit_structure__adds_to_substit_si_ajout_npd();
select test_substit_structure__deletes_substit_si_plus_doublon();
/*
select * from substit_log;
select * from structure_substit order by to_id, id;

select substit_create_all_substitutions_structure(20); -- totalité : 23-24 min (avec ou sans les raise)

select * from v_structure_doublon
where nom_patronymique in ('HOCHAN', 'VIEILLE', 'BERNAUDIN', 'BRANDLE DE MOTTA', 'DEMOULIN', 'DURET')
order by nom_patronymique;
*/


-- ------------------------------------------------------------------------------------------------------------------
-- Tests : etablissement
-- ------------------------------------------------------------------------------------------------------------------

select test_substit_etab__fetches_data_for_substituant();
select test_substit_etab__creates_substit_2_doublons();
select test_substit_etab__removes_from_substit_si_historise();
select test_substit_etab__adds_to_substit_si_dehistorise();
select test_substit_etab__removes_from_substit_si_source_app();
select test_substit_etab__removes_from_substit_si_plus_source_app();
select test_substit_etab__adds_to_substit_si_npd_force(); -- NB : NPD forcé sur pre_etablissement seulement (pas sur pre_structure)
select test_substit_etab__adds_to_substit_si_ajout_npd();
select test_substit_etab__deletes_substit_si_plus_doublon();
/*
select test_substit_etab__creates_substit_2_doublons();
select substit_npd_structure(pre.*) from pre_structure pre where code='ETABLE_HISMAN';
select substit_npd_structure(pre.*) from pre_structure pre order by id desc;
select substit_npd_etablissement(pre.*) from pre_etablissement pre order by id desc;
select * from pre_structure order by id desc;
select * from pre_etablissement order by id desc;
select * from v_structure_doublon order by id desc;
select * from structure_substit order by id desc;
select * from v_etablissement_doublon order by id desc;
select * from etablissement_substit order by id desc;
select * from structure order by id desc;
select * from etablissement order by id desc;
select * from substit_fetch_data_for_substituant_etablissement('etablissement,ETABLE_HISMAN');
*/


-- ------------------------------------------------------------------------------------------------------------------
-- Tests : ecole_doct
-- ------------------------------------------------------------------------------------------------------------------

select test_substit_ecole_doct__fetches_data_for_substituant();
select test_substit_ecole_doct__creates_substit_2_doublons();
select test_substit_ecole_doct__removes_from_substit_si_historise();
select test_substit_ecole_doct__adds_to_substit_si_dehistorise();
select test_substit_ecole_doct__removes_from_substit_si_source_app();
select test_substit_ecole_doct__removes_from_substit_si_plus_source_ap();
select test_substit_ecole_doct__adds_to_substit_si_npd_force(); -- NB : NPD forcé sur pre_ecole_doct seulement (pas sur pre_structure)
select test_substit_ecole_doct__adds_to_substit_si_ajout_npd();
select test_substit_ecole_doct__deletes_substit_si_plus_doublon();


-- ------------------------------------------------------------------------------------------------------------------
-- Tests : unite_rech
-- ------------------------------------------------------------------------------------------------------------------

select test_substit_unite_rech__fetches_data_for_substituant();
select test_substit_unite_rech__creates_substit_2_doublons();
select test_substit_unite_rech__removes_from_substit_si_historise();
select test_substit_unite_rech__adds_to_substit_si_dehistorise();
select test_substit_unite_rech__removes_from_substit_si_source_app();
select test_substit_unite_rech__removes_from_substit_si_plus_source_ap();
select test_substit_unite_rech__adds_to_substit_si_npd_force(); -- NB : NPD forcé sur pre_unite_rech seulement (pas sur pre_structure)
select test_substit_unite_rech__adds_to_substit_si_ajout_npd();
select test_substit_unite_rech__deletes_substit_si_plus_doublon();



-- ------------------------------------------------------------------------------------------------------------------
-- Tests : individu
-- ------------------------------------------------------------------------------------------------------------------

select test_substit_individu__fetches_data_for_substituant();
select test_substit_individu__creates_substit_2_doublons();
select test_substit_individu__creates_substit_3_doublons();
select test_substit_individu__creates_substit_2_doublons_insert_select();
select test_substit_individu__creates_substit_and_replaces_fk();
select test_substit_individu__creates_substit_can_fail_to_replace_fk();
select test_substit_individu__updates_substits_si_modif_nom();
select test_substit_individu__adds_to_substit_si_npd_force();
select test_substit_individu__adds_to_substit_si_dehistorise();
select test_substit_individu__adds_to_substit_si_ajout_npd();
select test_substit_individu__adds_to_substit_si_suppr_npd();
select test_substit_individu__substituant_update_enabled();
select test_substit_individu__removes_from_substit_si_historise();
select test_substit_individu__removes_from_substit_si_source_app();
select test_substit_individu__removes_from_substit_si_plus_source_app();
select test_substit_individu__removes_from_substit_and_restores_fk();
select test_substit_individu__deletes_substit_si_plus_doublon();
/*
select * from substit_log;
select * from structure_substit order by to_id, id;
select * from etablissement_substit order by to_id, id;
select * from pre_etablissement where nom_patronymique in ('HOCHON','HÔCHON','HOCHAN','COCHON') and histo_destruction is null order by source_code;
select * from src_etablissement where nom_patronymique in ('HOCHON','HÔCHON','HOCHAN','COCHON') and histo_destruction is null order by source_code;
select * from etablissement where nom_patronymique in ('HOCHON','HÔCHON','HOCHAN','COCHON') and histo_destruction is null order by source_code;
select i.id, v.* from v_diff_etablissement v join pre_etablissement i on v.source_code = i.source_code;

select substit_create_all_substitutions_etablissement(20); -- totalité : 23-24 min (avec ou sans les raise)
*/


-- ------------------------------------------------------------------------------------------------------------------
-- Tests : doctorant
-- ------------------------------------------------------------------------------------------------------------------

select test_substit_doctorant__finds_doublon_ssi_doublon_individu();
select test_substit_doctorant__fetches_data_for_substituant();
select test_substit_doctorant__creates_substit_2_doublons();
select test_substit_doctorant__creates_substit_3_doublons();
select test_substit_doctorant__creates_substit_and_replaces_fk();
select test_substit_doctorant__substituant_update_enabled();
select test_substit_doctorant__removes_from_substit_si_historise();
select test_substit_doctorant__adds_to_substit_si_dehistorise();
select test_substit_doctorant__removes_from_substit_si_source_app();
select test_substit_doctorant__removes_from_substit_si_plus_source_app();
select test_substit_doctorant__adds_to_substit_si_npd_force();
select test_substit_doctorant__updates_substits_si_modif_ine();
select test_substit_doctorant__adds_to_substit_si_ajout_npd();
select test_substit_doctorant__deletes_substit_si_plus_doublon();
/*
select * from substit_log;
select * from individu_substit order by to_id, id;
select * from doctorant_substit order by to_id, id;
select * from pre_doctorant where nom_patronymique in ('HOCHON','HÔCHON','HOCHAN','COCHON') and histo_destruction is null order by source_code;
select * from src_doctorant where nom_patronymique in ('HOCHON','HÔCHON','HOCHAN','COCHON') and histo_destruction is null order by source_code;
select * from doctorant where nom_patronymique in ('HOCHON','HÔCHON','HOCHAN','COCHON') and histo_destruction is null order by source_code;
select i.id, v.* from v_diff_doctorant v join pre_doctorant i on v.source_code = i.source_code;

select substit_create_all_substitutions_doctorant(20); -- totalité : 23-24 min (avec ou sans les raise)
*/






-- ------------------------------------------------------------------------------------------------------------------
-- Clean
-- ------------------------------------------------------------------------------------------------------------------
-- select 'drop function if exists '||routine_name||';' from information_schema.routines where routine_name ilike 'test_substit%';

drop function if exists test_substit_individu__set_up;
drop function if exists test_substit_individu__tear_down;
drop function if exists test_substit_individu__fetches_data_for_substituant;
drop function if exists test_substit_individu__updates_substits_si_modif_nom;
drop function if exists test_substit_individu__creates_substit_2_doublons;
drop function if exists test_substit_individu__creates_substit_and_replaces_fk;
drop function if exists test_substit_individu__adds_to_substit_si_suppr_npd;
drop function if exists test_substit_individu__creates_substit_can_fail_to_replace_fk;
drop function if exists test_substit_doctorant__set_up;
drop function if exists test_substit_individu__creates_substit_3_doublons;
drop function if exists test_substit_individu__creates_substit_2_doublons_insert_select;
drop function if exists test_substit_structure__fetches_data_for_substituant;
drop function if exists test_substit_doctorant__deletes_substit_si_plus_doublon;
drop function if exists test_substit_structure__set_up;
drop function if exists test_substit_structure__tear_down;
drop function if exists test_substit_etab__set_up;
drop function if exists test_substit_etab__tear_down;
drop function if exists test_substit_structure__adds_to_substit_si_dehistorise;
drop function if exists test_substit_ecole_doct__set_up;
drop function if exists test_substit_ecole_doct__tear_down;
drop function if exists test_substit_unite_rech__set_up;
drop function if exists test_substit_unite_rech__tear_down;
drop function if exists test_substit_unite_rech__removes_from_substit_si_plus_source_ap;
drop function if exists test_substit_individu__substituant_update_enabled;
drop function if exists test_substit_individu__removes_from_substit_si_historise;
drop function if exists test_substit_individu__removes_from_substit_and_restores_fk;
drop function if exists test_substit_individu__adds_to_substit_si_dehistorise;
drop function if exists test_substit_individu__removes_from_substit_si_source_app;
drop function if exists test_substit_individu__removes_from_substit_si_plus_source_app;
drop function if exists test_substit_individu__adds_to_substit_si_npd_force;
drop function if exists test_substit_individu__adds_to_substit_si_ajout_npd;
drop function if exists test_substit_individu__deletes_substit_si_plus_doublon;
drop function if exists test_substit_doctorant__finds_doublon_ssi_doublon_individu;
drop function if exists test_substit_doctorant__tear_down;
drop function if exists test_substit_doctorant__creates_substit_2_doublons;
drop function if exists test_substit_structure__removes_from_substit_si_historise;
drop function if exists test_substit_structure__creates_substit_2_doublons;
drop function if exists test_substit_structure__creates_substit_3_doublons;
drop function if exists test_substit_etab__fetches_data_for_substituant;
drop function if exists test_substit_etab__creates_substit_2_doublons;
drop function if exists test_substit_etab__removes_from_substit_si_historise;
drop function if exists test_substit_etab__adds_to_substit_si_dehistorise;
drop function if exists test_substit_etab__removes_from_substit_si_source_app;
drop function if exists test_substit_structure__removes_from_substit_si_plus_source_app;
drop function if exists test_substit_etab__removes_from_substit_si_plus_source_app;
drop function if exists test_substit_etab__adds_to_substit_si_npd_force;
drop function if exists test_substit_etab__adds_to_substit_si_ajout_npd;
drop function if exists test_substit_etab__deletes_substit_si_plus_doublon;
drop function if exists test_substit_ecole_doct__fetches_data_for_substituant;
drop function if exists test_substit_ecole_doct__creates_substit_2_doublons;
drop function if exists test_substit_ecole_doct__removes_from_substit_si_historise;
drop function if exists test_substit_ecole_doct__adds_to_substit_si_dehistorise;
drop function if exists test_substit_ecole_doct__removes_from_substit_si_source_app;
drop function if exists test_substit_ecole_doct__removes_from_substit_si_plus_source_ap;
drop function if exists test_substit_ecole_doct__adds_to_substit_si_npd_force;
drop function if exists test_substit_ecole_doct__adds_to_substit_si_ajout_npd;
drop function if exists test_substit_ecole_doct__deletes_substit_si_plus_doublon;
drop function if exists test_substit_unite_rech__fetches_data_for_substituant;
drop function if exists test_substit_unite_rech__creates_substit_2_doublons;
drop function if exists test_substit_unite_rech__removes_from_substit_si_historise;
drop function if exists test_substit_unite_rech__adds_to_substit_si_dehistorise;
drop function if exists test_substit_unite_rech__removes_from_substit_si_source_app;
drop function if exists test_substit_unite_rech__adds_to_substit_si_npd_force;
drop function if exists test_substit_unite_rech__adds_to_substit_si_ajout_npd;
drop function if exists test_substit_unite_rech__deletes_substit_si_plus_doublon;
drop function if exists test_substit_doctorant__creates_substit_3_doublons;
drop function if exists test_substit_doctorant__creates_substit_and_replaces_fk;
drop function if exists test_substit_structure__removes_from_substit_si_source_app;
drop function if exists test_substit_structure__adds_to_substit_si_npd_force;
drop function if exists test_substit_structure__updates_substits_si_modif_code;
drop function if exists test_substit_structure__adds_to_substit_si_ajout_npd;
drop function if exists test_substit_structure__deletes_substit_si_plus_doublon;
drop function if exists test_substit_doctorant__fetches_data_for_substituant;
drop function if exists test_substit_doctorant__substituant_update_enabled;
drop function if exists test_substit_doctorant__removes_from_substit_si_historise;
drop function if exists test_substit_doctorant__adds_to_substit_si_dehistorise;
drop function if exists test_substit_doctorant__removes_from_substit_si_source_app;
drop function if exists test_substit_doctorant__removes_from_substit_si_plus_source_app;
drop function if exists test_substit_doctorant__adds_to_substit_si_npd_force;
drop function if exists test_substit_doctorant__updates_substits_si_modif_ine;
drop function if exists test_substit_doctorant__adds_to_substit_si_ajout_npd;
