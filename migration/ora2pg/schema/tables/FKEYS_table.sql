-- Generated by Ora2Pg, the Oracle database Schema converter, version 20.0
-- Copyright 2000-2019 Gilles DAROLD. All rights reserved.
-- DATASOURCE: dbi:Oracle:host=sygaldb.unicaen.fr;sid=SYGLPROD;port=1523

SET client_encoding TO 'UTF8';

\set ON_ERROR_STOP ON

ALTER TABLE validation ADD CONSTRAINT validation_these_fk FOREIGN KEY (these_id) REFERENCES these(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE validation ADD CONSTRAINT validation_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE validation ADD CONSTRAINT validation_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE validation ADD CONSTRAINT validation_hcfk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE validation ADD CONSTRAINT validation_individu_id_fk FOREIGN KEY (individu_id) REFERENCES individu(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE validation ADD CONSTRAINT validation_type_validation_fk FOREIGN KEY (type_validation_id) REFERENCES type_validation(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE import_observ_result ADD CONSTRAINT import_observ_result_ioe_fk FOREIGN KEY (import_observ_id) REFERENCES import_observ(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE etablissement_rattach ADD CONSTRAINT rattachement_etab_id FOREIGN KEY (etablissement_id) REFERENCES etablissement(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE etablissement_rattach ADD CONSTRAINT rattachement_unite_id FOREIGN KEY (unite_id) REFERENCES unite_rech(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE privilege ADD CONSTRAINT sys_c006123 FOREIGN KEY (categorie_id) REFERENCES categorie_privilege(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE soutenance_justificatif ADD CONSTRAINT justificatif_createur_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_justificatif ADD CONSTRAINT justificatif_fichier_fk FOREIGN KEY (fichier_id) REFERENCES fichier_these(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_justificatif ADD CONSTRAINT justificatif_destructeur_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_justificatif ADD CONSTRAINT justificatif_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_justificatif ADD CONSTRAINT justificatif_membre_fk FOREIGN KEY (membre_id) REFERENCES soutenance_membre(id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_justificatif ADD CONSTRAINT justificatif_proposition_fk FOREIGN KEY (proposition_id) REFERENCES soutenance_proposition(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE soutenance_intervention ADD CONSTRAINT sintervention_userm_id_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_intervention ADD CONSTRAINT sintervention_these_id_fk FOREIGN KEY (these_id) REFERENCES these(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_intervention ADD CONSTRAINT sintervention_userc_id_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_intervention ADD CONSTRAINT sintervention_userd_id_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE soutenance_avis ADD CONSTRAINT avis_membre_fk FOREIGN KEY (membre_id) REFERENCES soutenance_membre(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_avis ADD CONSTRAINT avis_destructeur_id FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_avis ADD CONSTRAINT avis_fichier_id_fk FOREIGN KEY (fichier_id) REFERENCES fichier(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_avis ADD CONSTRAINT avis_validation_fk FOREIGN KEY (validation_id) REFERENCES validation(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_avis ADD CONSTRAINT avis_proposition_id FOREIGN KEY (proposition_id) REFERENCES soutenance_proposition(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_avis ADD CONSTRAINT avis_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE profil ADD CONSTRAINT rolemodele_structuretype_fk FOREIGN KEY (structure_type) REFERENCES type_structure(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE information ADD CONSTRAINT information_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE information ADD CONSTRAINT information_langue_id_fk FOREIGN KEY (langue_id) REFERENCES information_langue(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE information ADD CONSTRAINT information_hcfk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE information ADD CONSTRAINT information_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE doctorant_compl ADD CONSTRAINT thesard_compl_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE doctorant_compl ADD CONSTRAINT thesard_compl_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE doctorant_compl ADD CONSTRAINT doctorant_compl_doctorant_fk FOREIGN KEY (doctorant_id) REFERENCES doctorant(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE doctorant_compl ADD CONSTRAINT thesard_compl_hcfk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE diffusion ADD CONSTRAINT mise_en_ligne_these_fk FOREIGN KEY (these_id) REFERENCES these(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE diffusion ADD CONSTRAINT diffusion_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE diffusion ADD CONSTRAINT diffusion_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE diffusion ADD CONSTRAINT diffusion_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE individu_role ADD CONSTRAINT individu_role_ind_id_fk FOREIGN KEY (individu_id) REFERENCES individu(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE individu_role ADD CONSTRAINT individu_role_role_id_fk FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE soutenance_qualite ADD CONSTRAINT squalite_utilisateur_id_fk_2 FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_qualite ADD CONSTRAINT squalite_utilisateur_id_fk_3 FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_qualite ADD CONSTRAINT squalite_utilisateur_id_fk_1 FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE origine_financement ADD CONSTRAINT origine_financement_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE origine_financement ADD CONSTRAINT origine_financement_hcfk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE origine_financement ADD CONSTRAINT origine_financement_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE origine_financement ADD CONSTRAINT origine_financement_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE role ADD CONSTRAINT role_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE role ADD CONSTRAINT role_type_struct_id_fk FOREIGN KEY (type_structure_dependant_id) REFERENCES type_structure(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE role ADD CONSTRAINT role_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE role ADD CONSTRAINT role_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE role ADD CONSTRAINT role_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE role ADD CONSTRAINT role_structure_id_fk FOREIGN KEY (structure_id) REFERENCES structure(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE validite_fichier ADD CONSTRAINT validite_fichier_hcfk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE validite_fichier ADD CONSTRAINT validite_fichier_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE validite_fichier ADD CONSTRAINT validite_fichier_ffk FOREIGN KEY (fichier_id) REFERENCES fichier(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE validite_fichier ADD CONSTRAINT validite_fichier_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE structure_substit ADD CONSTRAINT str_substit_createur_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE structure_substit ADD CONSTRAINT str_substit_destructeur_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE structure_substit ADD CONSTRAINT str_substit_str_from_fk FOREIGN KEY (from_structure_id) REFERENCES structure(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE structure_substit ADD CONSTRAINT str_substit_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE structure_substit ADD CONSTRAINT str_substit_str_to_fk FOREIGN KEY (to_structure_id) REFERENCES structure(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE import_obs_notif ADD CONSTRAINT iond__n_fk FOREIGN KEY (notif_id) REFERENCES notif(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE import_obs_notif ADD CONSTRAINT iond__io_fk FOREIGN KEY (import_observ_id) REFERENCES import_observ(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE soutenance_proposition ADD CONSTRAINT proposition_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_proposition ADD CONSTRAINT proposition_these_fk FOREIGN KEY (these_id) REFERENCES these(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_proposition ADD CONSTRAINT proposition_createur_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_proposition ADD CONSTRAINT proposition_destructeur_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_proposition ADD CONSTRAINT soutenance_etat_id_fk FOREIGN KEY (etat_id) REFERENCES soutenance_etat(id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE profil_privilege ADD CONSTRAINT profil_privilege_profil_id_fk FOREIGN KEY (profil_id) REFERENCES profil(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE profil_privilege ADD CONSTRAINT role_priv_mod_priv_id_fk FOREIGN KEY (privilege_id) REFERENCES privilege(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE rapport_annuel ADD CONSTRAINT rapport_annuel_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE rapport_annuel ADD CONSTRAINT rapport_annuel_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE rapport_annuel ADD CONSTRAINT rapport_annuel_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE rapport_annuel ADD CONSTRAINT rapport_annuel_these_fk FOREIGN KEY (these_id) REFERENCES these(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE rapport_annuel ADD CONSTRAINT rapport_annuel_fichier_fk FOREIGN KEY (fichier_id) REFERENCES fichier(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE utilisateur ADD CONSTRAINT utilisateur_individu_fk FOREIGN KEY (individu_id) REFERENCES individu(id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE unite_rech ADD CONSTRAINT unite_rech_compl_hcfk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE unite_rech ADD CONSTRAINT unite_rech_struct_fk FOREIGN KEY (structure_id) REFERENCES structure(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE unite_rech ADD CONSTRAINT unite_rech_compl_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE unite_rech ADD CONSTRAINT unite_rech_compl_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE unite_rech ADD CONSTRAINT unite_rech_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE attestation ADD CONSTRAINT attestation_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE attestation ADD CONSTRAINT attestation_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE attestation ADD CONSTRAINT attestation_these_fk FOREIGN KEY (these_id) REFERENCES these(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE attestation ADD CONSTRAINT attestation_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE source ADD CONSTRAINT source_etablissement_id_fk FOREIGN KEY (etablissement_id) REFERENCES etablissement(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE these_annee_univ ADD CONSTRAINT these_annee_univ_these_id_fk FOREIGN KEY (these_id) REFERENCES these(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE these_annee_univ ADD CONSTRAINT these_annee_univ_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE these_annee_univ ADD CONSTRAINT these_annee_univ_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE these_annee_univ ADD CONSTRAINT these_annee_univ_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE these_annee_univ ADD CONSTRAINT these_annee_univ_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE structure ADD CONSTRAINT structure_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE structure ADD CONSTRAINT structure_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE structure ADD CONSTRAINT structure_type_structure_id_fk FOREIGN KEY (type_structure_id) REFERENCES type_structure(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE structure ADD CONSTRAINT structure_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE structure ADD CONSTRAINT structure_hcfk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE role_privilege ADD CONSTRAINT role_privilege_priv_id_fk FOREIGN KEY (privilege_id) REFERENCES privilege(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE role_privilege ADD CONSTRAINT role_privilege_role_id_fk FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE fichier_these ADD CONSTRAINT fichier_these_these_fk FOREIGN KEY (these_id) REFERENCES these(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE fichier_these ADD CONSTRAINT fichier_these_fichier_fk FOREIGN KEY (fichier_id) REFERENCES fichier(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE rdv_bu ADD CONSTRAINT rdv_bu_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE rdv_bu ADD CONSTRAINT rdv_bu_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE rdv_bu ADD CONSTRAINT rdv_bu_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE rdv_bu ADD CONSTRAINT rdv_bu_fk FOREIGN KEY (these_id) REFERENCES these(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE etablissement ADD CONSTRAINT etab_util_destructeur_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE etablissement ADD CONSTRAINT etab_struct_fk FOREIGN KEY (structure_id) REFERENCES structure(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE etablissement ADD CONSTRAINT etablissement_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE etablissement ADD CONSTRAINT etab_util_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE etablissement ADD CONSTRAINT etab_util_createur_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE etablissement ADD CONSTRAINT etablissement_fichier_id_fk FOREIGN KEY (signature_convocation_id) REFERENCES fichier(id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE titre_acces ADD CONSTRAINT titre_acces_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE titre_acces ADD CONSTRAINT titre_acces_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE titre_acces ADD CONSTRAINT titre_acces_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE titre_acces ADD CONSTRAINT titre_acces_these_id_fk FOREIGN KEY (these_id) REFERENCES these(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE titre_acces ADD CONSTRAINT titre_acces_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE soutenance_membre ADD CONSTRAINT membre_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_membre ADD CONSTRAINT membre_destructeur_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_membre ADD CONSTRAINT membre_proposition_fk FOREIGN KEY (proposition_id) REFERENCES soutenance_proposition(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_membre ADD CONSTRAINT membre_createur_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_membre ADD CONSTRAINT membre_qualite_fk FOREIGN KEY (qualite) REFERENCES soutenance_qualite(id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_membre ADD CONSTRAINT soutemembre_acteur_fk FOREIGN KEY (acteur_id) REFERENCES acteur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE variable ADD CONSTRAINT variable_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE variable ADD CONSTRAINT variable_hm_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE variable ADD CONSTRAINT variable_etab_fk FOREIGN KEY (etablissement_id) REFERENCES etablissement(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE variable ADD CONSTRAINT variable_hd_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE variable ADD CONSTRAINT variable_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE liste_diff ADD CONSTRAINT liste_diff_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE liste_diff ADD CONSTRAINT liste_diff_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE liste_diff ADD CONSTRAINT liste_diff_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE mail_confirmation ADD CONSTRAINT mailconfirmation_individuid_fk FOREIGN KEY (individu_id) REFERENCES individu(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE acteur ADD CONSTRAINT acteur_these_fk FOREIGN KEY (these_id) REFERENCES these(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE acteur ADD CONSTRAINT acteur_etab_id_fk FOREIGN KEY (acteur_etablissement_id) REFERENCES etablissement(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE acteur ADD CONSTRAINT acteur_indiv_fk FOREIGN KEY (individu_id) REFERENCES individu(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE acteur ADD CONSTRAINT acteur_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE acteur ADD CONSTRAINT acteur_role_id_fk FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE acteur ADD CONSTRAINT acteur_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE acteur ADD CONSTRAINT acteur_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE acteur ADD CONSTRAINT acteur_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE fichier ADD CONSTRAINT fichier_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE fichier ADD CONSTRAINT fichier_version_fk FOREIGN KEY (version_fichier_id) REFERENCES version_fichier(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE fichier ADD CONSTRAINT fichier_nature_fic_id_fk FOREIGN KEY (nature_id) REFERENCES nature_fichier(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE fichier ADD CONSTRAINT fichier_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE fichier ADD CONSTRAINT fichier_hcfk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE notif_result ADD CONSTRAINT notif_result__notif_fk FOREIGN KEY (notif_id) REFERENCES notif(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE ecole_doct ADD CONSTRAINT ecole_doct_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE ecole_doct ADD CONSTRAINT ecole_doct_hcfk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE ecole_doct ADD CONSTRAINT ecole_doct_struct_fk FOREIGN KEY (structure_id) REFERENCES structure(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE ecole_doct ADD CONSTRAINT ecole_doct_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE ecole_doct ADD CONSTRAINT ecole_doct_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE individu ADD CONSTRAINT individu_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE individu ADD CONSTRAINT individu_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE individu ADD CONSTRAINT individu_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE individu ADD CONSTRAINT individu_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE doctorant ADD CONSTRAINT doctorant_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE doctorant ADD CONSTRAINT doctorant_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE doctorant ADD CONSTRAINT doctorant_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE doctorant ADD CONSTRAINT doctorant_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE doctorant ADD CONSTRAINT doctorant_indiv_fk FOREIGN KEY (individu_id) REFERENCES individu(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE doctorant ADD CONSTRAINT doctorant_etab_fk FOREIGN KEY (etablissement_id) REFERENCES etablissement(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE these ADD CONSTRAINT these_unite_rech_fk FOREIGN KEY (unite_rech_id) REFERENCES unite_rech(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE these ADD CONSTRAINT these_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE these ADD CONSTRAINT these_doctorant_fk FOREIGN KEY (doctorant_id) REFERENCES doctorant(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE these ADD CONSTRAINT these_etab_fk FOREIGN KEY (etablissement_id) REFERENCES etablissement(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE these ADD CONSTRAINT these_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE these ADD CONSTRAINT these_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE these ADD CONSTRAINT these_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE these ADD CONSTRAINT these_ecole_doct_fk FOREIGN KEY (ecole_doct_id) REFERENCES ecole_doct(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE profil_to_role ADD CONSTRAINT profil_to_role_role_id_fk FOREIGN KEY (role_id) REFERENCES role(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE profil_to_role ADD CONSTRAINT profil_to_role_profil_id_fk FOREIGN KEY (profil_id) REFERENCES profil(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE financement ADD CONSTRAINT financement_source_fk FOREIGN KEY (source_id) REFERENCES source(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE financement ADD CONSTRAINT financement_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE financement ADD CONSTRAINT financement_hcfk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE financement ADD CONSTRAINT financement_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE metadonnee_these ADD CONSTRAINT metadonnee_these_these_id_fk FOREIGN KEY (these_id) REFERENCES these(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE import_obs_result_notif ADD CONSTRAINT iornr__nr_fk FOREIGN KEY (notif_result_id) REFERENCES notif_result(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
ALTER TABLE soutenance_qualite_sup ADD CONSTRAINT sqs_destructeur_fk FOREIGN KEY (histo_destructeur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_qualite_sup ADD CONSTRAINT sqs_createur_fk FOREIGN KEY (histo_createur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_qualite_sup ADD CONSTRAINT sqs_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES utilisateur(id) ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE;

ALTER TABLE soutenance_qualite_sup ADD CONSTRAINT sqs_qualite_fk FOREIGN KEY (qualite_id) REFERENCES soutenance_qualite(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE;
