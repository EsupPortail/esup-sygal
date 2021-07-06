
drop view if exists src_acteur cascade;
drop view if exists src_doctorant cascade;
drop view if exists src_ecole_doct cascade;
drop view if exists src_etablissement cascade;
drop view if exists src_financement cascade;
drop view if exists src_individu cascade;
drop view if exists src_origine_financement cascade;
drop view if exists src_role cascade;
drop view if exists src_structure cascade;
drop view if exists src_these cascade;
drop view if exists src_these_annee_univ cascade;
drop view if exists src_titre_acces cascade;
drop view if exists src_unite_rech cascade;
drop view if exists src_variable cascade;
drop view if exists v_diff_acteur cascade;
drop view if exists v_diff_doctorant cascade;
drop view if exists v_diff_ecole_doct cascade;
drop view if exists v_diff_etablissement cascade;
drop view if exists v_diff_financement cascade;
drop view if exists v_diff_individu cascade;
drop view if exists v_diff_role cascade;
drop view if exists v_diff_structure cascade;
drop view if exists v_diff_these cascade;
drop view if exists v_diff_these_annee_univ cascade;
drop view if exists v_diff_titre_acces cascade;
drop view if exists v_diff_unite_rech cascade;
drop view if exists v_diff_variable cascade;
drop view if exists v_situ_archivab_va cascade;
drop view if exists v_situ_archivab_vac cascade;
drop view if exists v_situ_archivab_vo cascade;
drop view if exists v_situ_archivab_voc cascade;
drop view if exists v_situ_attestations cascade;
drop view if exists v_situ_attestations_voc cascade;
drop view if exists v_situ_autoris_diff_these cascade;
drop view if exists v_situ_autoris_diff_these_voc cascade;
drop view if exists v_situ_depot_pv_sout cascade;
drop view if exists v_situ_depot_rapport_sout cascade;
drop view if exists v_situ_depot_va cascade;
drop view if exists v_situ_depot_vac cascade;
drop view if exists v_situ_depot_vc_valid_dir cascade;
drop view if exists v_situ_depot_vc_valid_doct cascade;
drop view if exists v_situ_depot_vc_valid_pres cascade;
drop view if exists v_situ_depot_vc_valid_pres_new cascade;
drop view if exists v_situ_depot_vo cascade;
drop view if exists v_situ_depot_voc cascade;
drop view if exists v_situ_rdv_bu_saisie_bu cascade;
drop view if exists v_situ_rdv_bu_saisie_doct cascade;
drop view if exists v_situ_rdv_bu_validation_bu cascade;
drop view if exists v_situ_signalement_these cascade;
drop view if exists v_situ_validation_page_couv cascade;
drop view if exists v_situ_verif_va cascade;
drop view if exists v_situ_verif_vac cascade;
drop view if exists v_situ_version_papier_corrigee cascade;
drop view if exists v_these_annee_univ_first cascade;
drop view if exists v_tmp_anomalie cascade;

drop materialized view if exists mv_recherche_these;

drop trigger if exists individu_rech_update on individu;

drop sequence if exists acteur_id_seq;
drop sequence if exists api_log_id_seq;
drop sequence if exists attestation_id_seq;
drop sequence if exists categorie_privilege_id_seq;
drop sequence if exists diffusion_id_seq;
drop sequence if exists doctorant_compl_id_seq;
drop sequence if exists doctorant_id_seq;
drop sequence if exists domaine_scientifique_id_seq;
drop sequence if exists ecole_doct_id_seq;
drop sequence if exists etablissement_id_seq;
drop sequence if exists etablissement_rattach_id_seq;
drop sequence if exists faq_id_seq;
drop sequence if exists fichier_id_seq;
drop sequence if exists fichier_these_id_seq;
drop sequence if exists financement_id_seq;
drop sequence if exists import_notif_id_seq;
drop sequence if exists import_observ_etab_id_seq;
drop sequence if exists import_observ_etab_resu_id_seq;
drop sequence if exists import_observ_id_seq;
drop sequence if exists import_observ_result_id_seq;
drop sequence if exists indicateur_id_seq;
drop sequence if exists individu_id_seq;
drop sequence if exists individu_role_id_seq;
drop sequence if exists information_fichier_id_seq;
drop sequence if exists information_id_seq;
drop sequence if exists liste_diff_id_seq;
drop sequence if exists mail_confirmation_id_seq;
drop sequence if exists metadonnee_these_id_seq;
drop sequence if exists nature_fichier_id_seq;
drop sequence if exists notif_id_seq;
drop sequence if exists notif_mail_id_seq;
drop sequence if exists notif_result_id_seq;
drop sequence if exists origine_financement_id_seq;
drop sequence if exists privilege_id_seq;
drop sequence if exists profil_id_seq;
drop sequence if exists rapport_annuel_id_seq;
drop sequence if exists rapport_id_seq;
drop sequence if exists rapport_validation_id_seq;
drop sequence if exists rdv_bu_id_seq;
drop sequence if exists role_id_seq;
drop sequence if exists soutenance_avis_id_seq;
drop sequence if exists soutenance_etat_id_seq;
drop sequence if exists soutenance_intervention_id_seq;
drop sequence if exists soutenance_justificatif_id_seq;
drop sequence if exists soutenance_membre_id_seq;
drop sequence if exists soutenance_proposition_id_seq;
drop sequence if exists soutenance_qualite_id_seq;
drop sequence if exists soutenance_qualite_sup_id_seq;
drop sequence if exists structure_document_id_seq;
drop sequence if exists structure_id_seq;
drop sequence if exists structure_substit_id_seq;
drop sequence if exists sync_log_id_seq;
drop sequence if exists synchro_log_id_seq;
drop sequence if exists these_annee_univ_id_seq;
drop sequence if exists these_id_seq;
drop sequence if exists titre_acces_id_seq;
drop sequence if exists type_validation_id_seq;
drop sequence if exists unite_rech_id_seq;
drop sequence if exists user_token_id_seq;
drop sequence if exists utilisateur_id_seq;
drop sequence if exists validation_id_seq;
drop sequence if exists validite_fichier_id_seq;
drop sequence if exists variable_id_seq;
drop sequence if exists wf_etape_id_seq;

drop table if exists acteur cascade ;
drop table if exists api_log cascade ;
drop table if exists attestation cascade ;
drop table if exists backup_fichier cascade ;
drop table if exists categorie_privilege cascade ;
drop table if exists diffusion cascade ;
drop table if exists doctorant cascade ;
drop table if exists doctorant_compl cascade ;
drop table if exists domaine_scientifique cascade ;
drop table if exists ecole_doct cascade ;
drop table if exists etablissement cascade ;
drop table if exists etablissement_rattach cascade ;
drop table if exists faq cascade ;
drop table if exists fichier cascade ;
drop table if exists fichier_sav cascade ;
drop table if exists fichier_these cascade ;
drop table if exists fichier_these_sav cascade ;
drop table if exists financement cascade ;
drop table if exists import_log cascade ;
drop table if exists import_notif cascade ;
drop table if exists import_obs_notif cascade ;
drop table if exists import_obs_result_notif cascade ;
drop table if exists import_observ cascade ;
drop table if exists import_observ_result cascade ;
drop table if exists indicateur cascade ;
drop table if exists individu cascade ;
drop table if exists individu_rech cascade ;
drop table if exists individu_role cascade ;
drop table if exists information cascade ;
drop table if exists information_fichier_sav cascade ;
drop table if exists information_langue cascade ;
drop table if exists liste_diff cascade ;
drop table if exists mail_confirmation cascade ;
drop table if exists metadonnee_these cascade ;
drop table if exists nature_fichier cascade ;
drop table if exists notif cascade ;
drop table if exists notif_mail cascade ;
drop table if exists notif_result cascade ;
drop table if exists origine_financement cascade ;
drop table if exists parametre cascade ;
drop table if exists privilege cascade ;
drop table if exists profil cascade ;
drop table if exists profil_privilege cascade ;
drop table if exists profil_to_role cascade ;
drop table if exists rapport cascade;
drop table if exists rapport_annuel cascade ;
drop table if exists rapport_validation cascade;
drop table if exists rdv_bu cascade ;
drop table if exists role cascade ;
drop table if exists role_privilege cascade ;
drop table if exists source cascade ;
drop table if exists soutenance_avis cascade ;
drop table if exists soutenance_configuration cascade ;
drop table if exists soutenance_etat cascade ;
drop table if exists soutenance_intervention cascade ;
drop table if exists soutenance_justificatif cascade ;
drop table if exists soutenance_membre cascade ;
drop table if exists soutenance_proposition cascade ;
drop table if exists soutenance_qualite cascade ;
drop table if exists soutenance_qualite_sup cascade ;
drop table if exists structure cascade ;
drop table if exists structure_document cascade;
drop table if exists structure_substit cascade ;
drop table if exists sync_log cascade ;
drop table if exists synchro_log cascade ;
drop table if exists these cascade ;
drop table if exists these_annee_univ cascade ;
drop table if exists titre_acces cascade ;
drop table if exists tmp_acteur cascade ;
drop table if exists tmp_doctorant cascade ;
drop table if exists tmp_ecole_doct cascade ;
drop table if exists tmp_etablissement cascade ;
drop table if exists tmp_financement cascade ;
drop table if exists tmp_individu cascade ;
drop table if exists tmp_origine_financement cascade ;
drop table if exists tmp_role cascade ;
drop table if exists tmp_structure cascade ;
drop table if exists tmp_these cascade ;
drop table if exists tmp_these_annee_univ cascade ;
drop table if exists tmp_titre_acces cascade ;
drop table if exists tmp_unite_rech cascade ;
drop table if exists tmp_variable cascade ;
drop table if exists type_rapport cascade;
drop table if exists type_structure cascade ;
drop table if exists type_validation cascade ;
drop table if exists unite_domaine_linker cascade ;
drop table if exists unite_rech cascade ;
drop table if exists user_token cascade;
drop table if exists utilisateur cascade ;
drop table if exists validation cascade ;
drop table if exists validite_fichier cascade ;
drop table if exists validite_fichier_sav cascade ;
drop table if exists variable cascade ;
drop table if exists version_fichier cascade ;
drop table if exists wf_etape cascade ;

drop function if exists comprise_entre(timestamp without time zone, timestamp without time zone, timestamp without time zone, numeric);
drop function if exists individu_haystack(text, text, text, text, text);
drop function if exists str_reduce(text);
drop function if exists trigger_fct_individu_rech_update();
