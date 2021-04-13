--
-- PostgreSQL database dump
--

-- Dumped from database version 13.0 (Debian 13.0-1.pgdg100+1)
-- Dumped by pg_dump version 13.2 (Ubuntu 13.2-1.pgdg20.04+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

--
-- Name: acteur acteur_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.acteur
    ADD CONSTRAINT acteur_pkey PRIMARY KEY (id);


--
-- Name: api_log api_log_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.api_log
    ADD CONSTRAINT api_log_pkey PRIMARY KEY (id);


--
-- Name: attestation attestation_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.attestation
    ADD CONSTRAINT attestation_pkey PRIMARY KEY (id);


--
-- Name: categorie_privilege categorie_privilege_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.categorie_privilege
    ADD CONSTRAINT categorie_privilege_pkey PRIMARY KEY (id);


--
-- Name: diffusion diffusion_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.diffusion
    ADD CONSTRAINT diffusion_pkey PRIMARY KEY (id);


--
-- Name: doctorant_compl doctorant_compl_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.doctorant_compl
    ADD CONSTRAINT doctorant_compl_pkey PRIMARY KEY (id);


--
-- Name: doctorant doctorant_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.doctorant
    ADD CONSTRAINT doctorant_pkey PRIMARY KEY (id);


--
-- Name: domaine_scientifique domaine_scientifique_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.domaine_scientifique
    ADD CONSTRAINT domaine_scientifique_pkey PRIMARY KEY (id);


--
-- Name: ecole_doct ecole_doct_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.ecole_doct
    ADD CONSTRAINT ecole_doct_pkey PRIMARY KEY (id);


--
-- Name: etablissement etablissement_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.etablissement
    ADD CONSTRAINT etablissement_pkey PRIMARY KEY (id);


--
-- Name: etablissement_rattach etablissement_rattach_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.etablissement_rattach
    ADD CONSTRAINT etablissement_rattach_pkey PRIMARY KEY (id);


--
-- Name: faq faq_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.faq
    ADD CONSTRAINT faq_pkey PRIMARY KEY (id);


--
-- Name: fichier fichier_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.fichier
    ADD CONSTRAINT fichier_pkey PRIMARY KEY (id);


--
-- Name: fichier_these fichier_these_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.fichier_these
    ADD CONSTRAINT fichier_these_pkey PRIMARY KEY (id);


--
-- Name: financement financement_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.financement
    ADD CONSTRAINT financement_pkey PRIMARY KEY (id);


--
-- Name: import_notif import_notif_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.import_notif
    ADD CONSTRAINT import_notif_pkey PRIMARY KEY (id);


--
-- Name: import_notif import_notif_table_name_column_name_operation_key; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.import_notif
    ADD CONSTRAINT import_notif_table_name_column_name_operation_key UNIQUE (table_name, column_name, operation);


--
-- Name: import_obs_notif import_obs_notif_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.import_obs_notif
    ADD CONSTRAINT import_obs_notif_pkey PRIMARY KEY (id);


--
-- Name: import_obs_result_notif import_obs_result_notif_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.import_obs_result_notif
    ADD CONSTRAINT import_obs_result_notif_pkey PRIMARY KEY (id);


--
-- Name: import_observ import_observ_code_key; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.import_observ
    ADD CONSTRAINT import_observ_code_key UNIQUE (code);


--
-- Name: import_observ import_observ_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.import_observ
    ADD CONSTRAINT import_observ_pkey PRIMARY KEY (id);


--
-- Name: import_observ_result import_observ_result_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.import_observ_result
    ADD CONSTRAINT import_observ_result_pkey PRIMARY KEY (id);


--
-- Name: import_observ import_observ_table_name_column_name_operation_to_value_key; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.import_observ
    ADD CONSTRAINT import_observ_table_name_column_name_operation_to_value_key UNIQUE (table_name, column_name, operation, to_value);


--
-- Name: indicateur indicateur_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.indicateur
    ADD CONSTRAINT indicateur_pkey PRIMARY KEY (id);


--
-- Name: individu individu_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.individu
    ADD CONSTRAINT individu_pkey PRIMARY KEY (id);


--
-- Name: individu_rech individu_rech_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.individu_rech
    ADD CONSTRAINT individu_rech_pkey PRIMARY KEY (id);


--
-- Name: individu_role individu_role_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.individu_role
    ADD CONSTRAINT individu_role_pkey PRIMARY KEY (id);


--
-- Name: information_fichier_sav information_fichier_sav_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.information_fichier_sav
    ADD CONSTRAINT information_fichier_sav_pkey PRIMARY KEY (id);


--
-- Name: information_langue information_langue_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.information_langue
    ADD CONSTRAINT information_langue_pkey PRIMARY KEY (id);


--
-- Name: information information_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.information
    ADD CONSTRAINT information_pkey PRIMARY KEY (id);


--
-- Name: liste_diff liste_diff_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.liste_diff
    ADD CONSTRAINT liste_diff_pkey PRIMARY KEY (id);


--
-- Name: mail_confirmation mail_confirmation_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.mail_confirmation
    ADD CONSTRAINT mail_confirmation_pkey PRIMARY KEY (id);


--
-- Name: metadonnee_these metadonnee_these_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.metadonnee_these
    ADD CONSTRAINT metadonnee_these_pkey PRIMARY KEY (id);


--
-- Name: nature_fichier nature_fichier_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.nature_fichier
    ADD CONSTRAINT nature_fichier_pkey PRIMARY KEY (id);


--
-- Name: notif notif_code_key; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.notif
    ADD CONSTRAINT notif_code_key UNIQUE (code);


--
-- Name: notif notif_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.notif
    ADD CONSTRAINT notif_pkey PRIMARY KEY (id);


--
-- Name: notif_result notif_result_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.notif_result
    ADD CONSTRAINT notif_result_pkey PRIMARY KEY (id);


--
-- Name: origine_financement origine_financement_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.origine_financement
    ADD CONSTRAINT origine_financement_pkey PRIMARY KEY (id);


--
-- Name: parametre parametre_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.parametre
    ADD CONSTRAINT parametre_pkey PRIMARY KEY (id);


--
-- Name: privilege privilege_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.privilege
    ADD CONSTRAINT privilege_pkey PRIMARY KEY (id);


--
-- Name: profil profil_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.profil
    ADD CONSTRAINT profil_pkey PRIMARY KEY (id);


--
-- Name: profil_privilege profil_privilege_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.profil_privilege
    ADD CONSTRAINT profil_privilege_pkey PRIMARY KEY (profil_id, privilege_id);


--
-- Name: profil_to_role profil_to_role_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.profil_to_role
    ADD CONSTRAINT profil_to_role_pkey PRIMARY KEY (profil_id, role_id);


--
-- Name: rapport_annuel rapport_annuel_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.rapport_annuel
    ADD CONSTRAINT rapport_annuel_pkey PRIMARY KEY (id);


--
-- Name: rdv_bu rdv_bu_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.rdv_bu
    ADD CONSTRAINT rdv_bu_pkey PRIMARY KEY (id);


--
-- Name: role role_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.role
    ADD CONSTRAINT role_pkey PRIMARY KEY (id);


--
-- Name: role_privilege role_privilege_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.role_privilege
    ADD CONSTRAINT role_privilege_pkey PRIMARY KEY (role_id, privilege_id);


--
-- Name: source source_code_key; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.source
    ADD CONSTRAINT source_code_key UNIQUE (code);


--
-- Name: source source_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.source
    ADD CONSTRAINT source_pkey PRIMARY KEY (id);


--
-- Name: soutenance_configuration soutenance_configuration_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_configuration
    ADD CONSTRAINT soutenance_configuration_pkey PRIMARY KEY (id);


--
-- Name: soutenance_etat soutenance_etat_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_etat
    ADD CONSTRAINT soutenance_etat_pkey PRIMARY KEY (id);


--
-- Name: soutenance_intervention soutenance_intervention_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_intervention
    ADD CONSTRAINT soutenance_intervention_pkey PRIMARY KEY (id);


--
-- Name: soutenance_justificatif soutenance_justificatif_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_justificatif
    ADD CONSTRAINT soutenance_justificatif_pkey PRIMARY KEY (id);


--
-- Name: soutenance_membre soutenance_membre_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_membre
    ADD CONSTRAINT soutenance_membre_pkey PRIMARY KEY (id);


--
-- Name: soutenance_proposition soutenance_proposition_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_proposition
    ADD CONSTRAINT soutenance_proposition_pkey PRIMARY KEY (id);


--
-- Name: soutenance_qualite soutenance_qualite_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_qualite
    ADD CONSTRAINT soutenance_qualite_pkey PRIMARY KEY (id);


--
-- Name: soutenance_qualite_sup soutenance_qualite_sup_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_qualite_sup
    ADD CONSTRAINT soutenance_qualite_sup_pkey PRIMARY KEY (id);


--
-- Name: structure structure_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.structure
    ADD CONSTRAINT structure_pkey PRIMARY KEY (id);


--
-- Name: structure_substit structure_substit_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.structure_substit
    ADD CONSTRAINT structure_substit_pkey PRIMARY KEY (id);


--
-- Name: sync_log sync_log_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.sync_log
    ADD CONSTRAINT sync_log_pkey PRIMARY KEY (id);


--
-- Name: synchro_log synchro_log_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.synchro_log
    ADD CONSTRAINT synchro_log_pkey PRIMARY KEY (id);


--
-- Name: these_annee_univ these_annee_univ_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these_annee_univ
    ADD CONSTRAINT these_annee_univ_pkey PRIMARY KEY (id);


--
-- Name: these these_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these
    ADD CONSTRAINT these_pkey PRIMARY KEY (id);


--
-- Name: titre_acces titre_acces_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.titre_acces
    ADD CONSTRAINT titre_acces_pkey PRIMARY KEY (id);


--
-- Name: type_structure type_structure_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.type_structure
    ADD CONSTRAINT type_structure_pkey PRIMARY KEY (id);


--
-- Name: type_validation type_validation_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.type_validation
    ADD CONSTRAINT type_validation_pkey PRIMARY KEY (id);


--
-- Name: unite_domaine_linker unite_domaine_linker_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.unite_domaine_linker
    ADD CONSTRAINT unite_domaine_linker_pkey PRIMARY KEY (unite_id, domaine_id);


--
-- Name: unite_rech unite_rech_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.unite_rech
    ADD CONSTRAINT unite_rech_pkey PRIMARY KEY (id);


--
-- Name: utilisateur utilisateur_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.utilisateur
    ADD CONSTRAINT utilisateur_pkey PRIMARY KEY (id);


--
-- Name: utilisateur utilisateur_username_key; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.utilisateur
    ADD CONSTRAINT utilisateur_username_key UNIQUE (username);


--
-- Name: validation validation_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.validation
    ADD CONSTRAINT validation_pkey PRIMARY KEY (id);


--
-- Name: validite_fichier validite_fichier_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.validite_fichier
    ADD CONSTRAINT validite_fichier_pkey PRIMARY KEY (id);


--
-- Name: variable variable_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.variable
    ADD CONSTRAINT variable_pkey PRIMARY KEY (id);


--
-- Name: version_fichier version_fichier_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.version_fichier
    ADD CONSTRAINT version_fichier_pkey PRIMARY KEY (id);


--
-- Name: wf_etape wf_etape_code_key; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.wf_etape
    ADD CONSTRAINT wf_etape_code_key UNIQUE (code);


--
-- Name: wf_etape wf_etape_ordre_key; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.wf_etape
    ADD CONSTRAINT wf_etape_ordre_key UNIQUE (ordre);


--
-- Name: wf_etape wf_etape_pkey; Type: CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.wf_etape
    ADD CONSTRAINT wf_etape_pkey PRIMARY KEY (id);


--
-- Name: acteur_acteur_etab_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX acteur_acteur_etab_id_idx ON public.acteur USING btree (acteur_etablissement_id);


--
-- Name: acteur_doctorant_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX acteur_doctorant_id_idx ON public.these USING btree (doctorant_id);


--
-- Name: acteur_ecole_doct_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX acteur_ecole_doct_id_idx ON public.these USING btree (ecole_doct_id);


--
-- Name: acteur_etablissement_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX acteur_etablissement_id_idx ON public.these USING btree (etablissement_id);


--
-- Name: acteur_histo_destruct_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX acteur_histo_destruct_id_idx ON public.acteur USING btree (histo_destructeur_id);


--
-- Name: acteur_histo_modif_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX acteur_histo_modif_id_idx ON public.acteur USING btree (histo_modificateur_id);


--
-- Name: acteur_individu_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX acteur_individu_id_idx ON public.acteur USING btree (individu_id);


--
-- Name: acteur_role_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX acteur_role_id_idx ON public.acteur USING btree (role_id);


--
-- Name: acteur_source_code_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX acteur_source_code_uniq ON public.acteur USING btree (source_code);


--
-- Name: acteur_source_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX acteur_source_id_idx ON public.acteur USING btree (source_id);


--
-- Name: acteur_these_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX acteur_these_id_idx ON public.acteur USING btree (these_id);


--
-- Name: acteur_unite_rech_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX acteur_unite_rech_id_idx ON public.these USING btree (unite_rech_id);


--
-- Name: attestation_hc_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX attestation_hc_idx ON public.attestation USING btree (histo_createur_id);


--
-- Name: attestation_hd_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX attestation_hd_idx ON public.attestation USING btree (histo_destructeur_id);


--
-- Name: attestation_hm_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX attestation_hm_idx ON public.attestation USING btree (histo_modificateur_id);


--
-- Name: attestation_these_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX attestation_these_idx ON public.attestation USING btree (these_id);


--
-- Name: categorie_privilege_unique; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX categorie_privilege_unique ON public.categorie_privilege USING btree (code);


--
-- Name: configuration_code_uindex; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX configuration_code_uindex ON public.soutenance_configuration USING btree (code);


--
-- Name: diffusion_hc_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX diffusion_hc_idx ON public.diffusion USING btree (histo_createur_id);


--
-- Name: diffusion_hd_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX diffusion_hd_idx ON public.diffusion USING btree (histo_destructeur_id);


--
-- Name: diffusion_hm_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX diffusion_hm_idx ON public.diffusion USING btree (histo_modificateur_id);


--
-- Name: diffusion_these_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX diffusion_these_idx ON public.diffusion USING btree (these_id);


--
-- Name: doctorant_compl_doctorant_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX doctorant_compl_doctorant_idx ON public.doctorant_compl USING btree (doctorant_id);


--
-- Name: doctorant_compl_hc_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX doctorant_compl_hc_idx ON public.doctorant_compl USING btree (histo_createur_id);


--
-- Name: doctorant_compl_hd_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX doctorant_compl_hd_idx ON public.doctorant_compl USING btree (histo_destructeur_id);


--
-- Name: doctorant_compl_hm_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX doctorant_compl_hm_idx ON public.doctorant_compl USING btree (histo_modificateur_id);


--
-- Name: doctorant_compl_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX doctorant_compl_un ON public.doctorant_compl USING btree (persopass, histo_destruction);


--
-- Name: doctorant_etablissement_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX doctorant_etablissement_idx ON public.doctorant USING btree (etablissement_id);


--
-- Name: doctorant_hcfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX doctorant_hcfk_idx ON public.doctorant USING btree (histo_createur_id);


--
-- Name: doctorant_hdfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX doctorant_hdfk_idx ON public.doctorant USING btree (histo_destructeur_id);


--
-- Name: doctorant_hmfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX doctorant_hmfk_idx ON public.doctorant USING btree (histo_modificateur_id);


--
-- Name: doctorant_individu_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX doctorant_individu_idx ON public.doctorant USING btree (individu_id);


--
-- Name: doctorant_source_code_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX doctorant_source_code_uniq ON public.doctorant USING btree (source_code);


--
-- Name: doctorant_src_id_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX doctorant_src_id_index ON public.doctorant USING btree (source_id);


--
-- Name: ecole_doct_hc_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX ecole_doct_hc_idx ON public.ecole_doct USING btree (histo_createur_id);


--
-- Name: ecole_doct_hd_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX ecole_doct_hd_idx ON public.ecole_doct USING btree (histo_destructeur_id);


--
-- Name: ecole_doct_hm_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX ecole_doct_hm_idx ON public.ecole_doct USING btree (histo_modificateur_id);


--
-- Name: ecole_doct_source_code_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX ecole_doct_source_code_un ON public.ecole_doct USING btree (source_code);


--
-- Name: ecole_doct_source_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX ecole_doct_source_idx ON public.ecole_doct USING btree (source_id);


--
-- Name: ecole_doct_struct_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX ecole_doct_struct_id_idx ON public.ecole_doct USING btree (structure_id);


--
-- Name: etablissement_domaine_uindex; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX etablissement_domaine_uindex ON public.etablissement USING btree (domaine);


--
-- Name: etablissement_struct_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX etablissement_struct_id_idx ON public.etablissement USING btree (structure_id);


--
-- Name: fichier_hcfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX fichier_hcfk_idx ON public.fichier USING btree (histo_createur_id);


--
-- Name: fichier_hdfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX fichier_hdfk_idx ON public.fichier USING btree (histo_destructeur_id);


--
-- Name: fichier_hmfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX fichier_hmfk_idx ON public.fichier USING btree (histo_modificateur_id);


--
-- Name: fichier_nature_id_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX fichier_nature_id_index ON public.fichier USING btree (nature_id);


--
-- Name: fichier_permanent_id_uindex; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX fichier_permanent_id_uindex ON public.fichier USING btree (permanent_id);


--
-- Name: fichier_these_fich_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX fichier_these_fich_id_idx ON public.fichier_these USING btree (fichier_id);


--
-- Name: fichier_these_these_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX fichier_these_these_id_idx ON public.fichier_these USING btree (these_id);


--
-- Name: fichier_uuid_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX fichier_uuid_un ON public.fichier USING btree (uuid);


--
-- Name: fichier_version_fk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX fichier_version_fk_idx ON public.fichier USING btree (version_fichier_id);


--
-- Name: financement_source_code_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX financement_source_code_un ON public.financement USING btree (source_code);


--
-- Name: import_notif_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX import_notif_un ON public.import_notif USING btree (table_name, column_name, operation);


--
-- Name: import_obs_notif_io_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX import_obs_notif_io_idx ON public.import_obs_notif USING btree (import_observ_id);


--
-- Name: import_obs_notif_ior_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX import_obs_notif_ior_idx ON public.import_obs_result_notif USING btree (import_observ_result_id);


--
-- Name: import_obs_notif_n_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX import_obs_notif_n_idx ON public.import_obs_notif USING btree (notif_id);


--
-- Name: import_obs_notif_nr_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX import_obs_notif_nr_idx ON public.import_obs_result_notif USING btree (notif_result_id);


--
-- Name: import_observ_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX import_observ_un ON public.import_observ USING btree (table_name, column_name, operation, to_value);


--
-- Name: individu_hcfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX individu_hcfk_idx ON public.individu USING btree (histo_createur_id);


--
-- Name: individu_hdfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX individu_hdfk_idx ON public.individu USING btree (histo_destructeur_id);


--
-- Name: individu_hmfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX individu_hmfk_idx ON public.individu USING btree (histo_modificateur_id);


--
-- Name: individu_role_individu_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX individu_role_individu_idx ON public.individu_role USING btree (individu_id);


--
-- Name: individu_role_role_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX individu_role_role_idx ON public.individu_role USING btree (role_id);


--
-- Name: individu_role_unique; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX individu_role_unique ON public.individu_role USING btree (individu_id, role_id);


--
-- Name: individu_source_code_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX individu_source_code_uniq ON public.individu USING btree (source_code, histo_destruction);


--
-- Name: individu_src_id_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX individu_src_id_index ON public.individu USING btree (source_id);


--
-- Name: information_filename_uindex; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX information_filename_uindex ON public.information_fichier_sav USING btree (filename);


--
-- Name: liste_diff_adresse_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX liste_diff_adresse_un ON public.liste_diff USING btree (adresse);


--
-- Name: mail_confirmation_code_uindex; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX mail_confirmation_code_uindex ON public.mail_confirmation USING btree (code);


--
-- Name: metadonnee_these_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX metadonnee_these_uniq ON public.metadonnee_these USING btree (these_id);


--
-- Name: notif_result_notif_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX notif_result_notif_idx ON public.notif_result USING btree (notif_id);


--
-- Name: origine_fin_source_code_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX origine_fin_source_code_un ON public.origine_financement USING btree (source_code);


--
-- Name: privilege_categ_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX privilege_categ_idx ON public.privilege USING btree (categorie_id);


--
-- Name: privilege_unique; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX privilege_unique ON public.privilege USING btree (categorie_id, code);


--
-- Name: profil_role_id_uindex; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX profil_role_id_uindex ON public.profil USING btree (role_id);


--
-- Name: rdv_bu_hc_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX rdv_bu_hc_idx ON public.rdv_bu USING btree (histo_createur_id);


--
-- Name: rdv_bu_hd_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX rdv_bu_hd_idx ON public.rdv_bu USING btree (histo_destructeur_id);


--
-- Name: rdv_bu_hm_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX rdv_bu_hm_idx ON public.rdv_bu USING btree (histo_modificateur_id);


--
-- Name: rdv_bu_these_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX rdv_bu_these_idx ON public.rdv_bu USING btree (these_id);


--
-- Name: role_privilege_privilege_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX role_privilege_privilege_idx ON public.role_privilege USING btree (privilege_id);


--
-- Name: role_privilege_role_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX role_privilege_role_idx ON public.role_privilege USING btree (role_id);


--
-- Name: role_source_code_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX role_source_code_un ON public.role USING btree (source_code);


--
-- Name: role_structure_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX role_structure_id_idx ON public.role USING btree (structure_id);


--
-- Name: role_type_structure_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX role_type_structure_id_idx ON public.role USING btree (type_structure_dependant_id);


--
-- Name: source_code_unique; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX source_code_unique ON public.etablissement USING btree (source_code);


--
-- Name: str_substit_str_to_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX str_substit_str_to_idx ON public.structure_substit USING btree (to_structure_id);


--
-- Name: str_substit_unique; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX str_substit_unique ON public.structure_substit USING btree (from_structure_id);


--
-- Name: structure_source_code_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX structure_source_code_un ON public.structure USING btree (source_code);


--
-- Name: structure_type_str_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX structure_type_str_id_idx ON public.structure USING btree (type_structure_id);


--
-- Name: these_an_univ_source_code_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX these_an_univ_source_code_un ON public.these_annee_univ USING btree (source_code);


--
-- Name: these_annee_univ_these_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX these_annee_univ_these_id_idx ON public.these_annee_univ USING btree (these_id);


--
-- Name: these_etat_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX these_etat_index ON public.these USING btree (etat_these);


--
-- Name: these_hcfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX these_hcfk_idx ON public.these USING btree (histo_createur_id);


--
-- Name: these_hdfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX these_hdfk_idx ON public.these USING btree (histo_destructeur_id);


--
-- Name: these_hmfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX these_hmfk_idx ON public.these USING btree (histo_modificateur_id);


--
-- Name: these_source_code_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX these_source_code_uniq ON public.these USING btree (source_code);


--
-- Name: these_src_id_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX these_src_id_index ON public.these USING btree (source_id);


--
-- Name: these_titre_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX these_titre_index ON public.these USING btree (titre);


--
-- Name: titre_acces_source_code_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX titre_acces_source_code_un ON public.titre_acces USING btree (source_code);


--
-- Name: titre_acces_these_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX titre_acces_these_id_idx ON public.titre_acces USING btree (these_id);


--
-- Name: tmp_acteur_source_code_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_acteur_source_code_index ON public.tmp_acteur USING btree (source_code);


--
-- Name: tmp_acteur_source_id_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_acteur_source_id_index ON public.tmp_acteur USING btree (source_id);


--
-- Name: tmp_acteur_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_acteur_uniq ON public.tmp_acteur USING btree (id, etablissement_id);


--
-- Name: tmp_doctorant_source_code_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_doctorant_source_code_idx ON public.tmp_doctorant USING btree (source_code);


--
-- Name: tmp_doctorant_source_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_doctorant_source_id_idx ON public.tmp_doctorant USING btree (source_id);


--
-- Name: tmp_doctorant_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_doctorant_uniq ON public.tmp_doctorant USING btree (id, etablissement_id);


--
-- Name: tmp_ecole_doct_source_code_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_ecole_doct_source_code_idx ON public.tmp_ecole_doct USING btree (source_code);


--
-- Name: tmp_ecole_doct_source_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_ecole_doct_source_id_idx ON public.tmp_ecole_doct USING btree (source_id);


--
-- Name: tmp_ecole_doct_struct_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_ecole_doct_struct_id_idx ON public.tmp_ecole_doct USING btree (structure_id);


--
-- Name: tmp_ecole_doct_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_ecole_doct_uniq ON public.tmp_ecole_doct USING btree (id, structure_id);


--
-- Name: tmp_etab_source_code_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_etab_source_code_idx ON public.tmp_etablissement USING btree (source_code);


--
-- Name: tmp_etab_source_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_etab_source_id_idx ON public.tmp_etablissement USING btree (source_id);


--
-- Name: tmp_etab_struct_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_etab_struct_id_idx ON public.tmp_etablissement USING btree (structure_id);


--
-- Name: tmp_etab_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_etab_uniq ON public.tmp_etablissement USING btree (id, structure_id);


--
-- Name: tmp_financement_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_financement_uniq ON public.tmp_financement USING btree (id, etablissement_id);


--
-- Name: tmp_individu_source_code_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_individu_source_code_idx ON public.tmp_individu USING btree (source_code);


--
-- Name: tmp_individu_source_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_individu_source_id_idx ON public.tmp_individu USING btree (source_id);


--
-- Name: tmp_individu_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_individu_uniq ON public.tmp_individu USING btree (id, etablissement_id);


--
-- Name: tmp_origine_financement_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_origine_financement_uniq ON public.tmp_origine_financement USING btree (id, etablissement_id);


--
-- Name: tmp_role_source_code_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_role_source_code_index ON public.tmp_role USING btree (source_code);


--
-- Name: tmp_role_source_id_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_role_source_id_index ON public.tmp_role USING btree (source_id);


--
-- Name: tmp_role_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_role_uniq ON public.tmp_role USING btree (id, etablissement_id);


--
-- Name: tmp_structure_source_code_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_structure_source_code_idx ON public.tmp_structure USING btree (source_code);


--
-- Name: tmp_structure_source_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_structure_source_id_idx ON public.tmp_structure USING btree (source_id);


--
-- Name: tmp_structure_type_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_structure_type_id_idx ON public.tmp_structure USING btree (type_structure_id);


--
-- Name: tmp_these_annee_u_src_cod_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_these_annee_u_src_cod_idx ON public.tmp_these_annee_univ USING btree (source_code);


--
-- Name: tmp_these_annee_u_src_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_these_annee_u_src_id_idx ON public.tmp_these_annee_univ USING btree (source_id);


--
-- Name: tmp_these_annee_u_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_these_annee_u_uniq ON public.tmp_these_annee_univ USING btree (id, etablissement_id);


--
-- Name: tmp_these_source_code_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_these_source_code_index ON public.tmp_these USING btree (source_code);


--
-- Name: tmp_these_source_id_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_these_source_id_index ON public.tmp_these USING btree (source_id);


--
-- Name: tmp_these_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_these_uniq ON public.tmp_these USING btree (id, etablissement_id);


--
-- Name: tmp_titre_acces_source_cod_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_titre_acces_source_cod_idx ON public.tmp_titre_acces USING btree (source_code);


--
-- Name: tmp_titre_acces_source_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_titre_acces_source_id_idx ON public.tmp_titre_acces USING btree (source_id);


--
-- Name: tmp_titre_acces_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_titre_acces_uniq ON public.tmp_titre_acces USING btree (id, etablissement_id);


--
-- Name: tmp_unite_rech_source_code_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_unite_rech_source_code_idx ON public.tmp_unite_rech USING btree (source_code);


--
-- Name: tmp_unite_rech_source_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_unite_rech_source_id_idx ON public.tmp_unite_rech USING btree (source_id);


--
-- Name: tmp_unite_rech_struct_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_unite_rech_struct_id_idx ON public.tmp_unite_rech USING btree (structure_id);


--
-- Name: tmp_unite_rech_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_unite_rech_uniq ON public.tmp_unite_rech USING btree (id, structure_id);


--
-- Name: tmp_variable_source_code_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_variable_source_code_index ON public.tmp_variable USING btree (source_code);


--
-- Name: tmp_variable_source_id_index; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX tmp_variable_source_id_index ON public.tmp_variable USING btree (source_id);


--
-- Name: tmp_variable_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX tmp_variable_uniq ON public.tmp_variable USING btree (id, etablissement_id);


--
-- Name: type_structure_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX type_structure_un ON public.type_structure USING btree (code);


--
-- Name: type_validation_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX type_validation_un ON public.type_validation USING btree (code);


--
-- Name: unite_rech_hc_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX unite_rech_hc_idx ON public.unite_rech USING btree (histo_createur_id);


--
-- Name: unite_rech_hd_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX unite_rech_hd_idx ON public.unite_rech USING btree (histo_destructeur_id);


--
-- Name: unite_rech_hm_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX unite_rech_hm_idx ON public.unite_rech USING btree (histo_modificateur_id);


--
-- Name: unite_rech_source_code_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX unite_rech_source_code_un ON public.unite_rech USING btree (source_code);


--
-- Name: unite_rech_source_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX unite_rech_source_idx ON public.unite_rech USING btree (source_id);


--
-- Name: unite_rech_struct_id_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX unite_rech_struct_id_idx ON public.unite_rech USING btree (structure_id);


--
-- Name: utilis_password_reset_token_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX utilis_password_reset_token_un ON public.utilisateur USING btree (password_reset_token);


--
-- Name: validation_hcfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX validation_hcfk_idx ON public.validation USING btree (histo_createur_id);


--
-- Name: validation_hdfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX validation_hdfk_idx ON public.validation USING btree (histo_destructeur_id);


--
-- Name: validation_hmfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX validation_hmfk_idx ON public.validation USING btree (histo_modificateur_id);


--
-- Name: validation_individu_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX validation_individu_idx ON public.validation USING btree (individu_id);


--
-- Name: validation_these_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX validation_these_idx ON public.validation USING btree (these_id);


--
-- Name: validation_type_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX validation_type_idx ON public.validation USING btree (type_validation_id);


--
-- Name: validation_un; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX validation_un ON public.validation USING btree (type_validation_id, these_id, histo_destruction, individu_id);


--
-- Name: validite_fichier_fichier_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX validite_fichier_fichier_idx ON public.validite_fichier USING btree (fichier_id);


--
-- Name: validite_fichier_hcfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX validite_fichier_hcfk_idx ON public.validite_fichier USING btree (histo_createur_id);


--
-- Name: validite_fichier_hdfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX validite_fichier_hdfk_idx ON public.validite_fichier USING btree (histo_destructeur_id);


--
-- Name: validite_fichier_hmfk_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX validite_fichier_hmfk_idx ON public.validite_fichier USING btree (histo_modificateur_id);


--
-- Name: variable_code_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX variable_code_uniq ON public.variable USING btree (code, etablissement_id);


--
-- Name: variable_etablissement_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX variable_etablissement_idx ON public.variable USING btree (etablissement_id);


--
-- Name: variable_hc_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX variable_hc_idx ON public.variable USING btree (histo_createur_id);


--
-- Name: variable_hd_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX variable_hd_idx ON public.variable USING btree (histo_destructeur_id);


--
-- Name: variable_hm_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX variable_hm_idx ON public.variable USING btree (histo_modificateur_id);


--
-- Name: variable_source_code_uniq; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX variable_source_code_uniq ON public.variable USING btree (source_code);


--
-- Name: variable_source_idx; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE INDEX variable_source_idx ON public.variable USING btree (source_id);


--
-- Name: version_fichier_uniq_code; Type: INDEX; Schema: public; Owner: ad_sygal_dev
--

CREATE UNIQUE INDEX version_fichier_uniq_code ON public.version_fichier USING btree (code);


--
-- Name: individu individu_rech_update; Type: TRIGGER; Schema: public; Owner: ad_sygal_dev
--

CREATE TRIGGER individu_rech_update AFTER INSERT OR DELETE OR UPDATE ON public.individu FOR EACH ROW EXECUTE FUNCTION public.trigger_fct_individu_rech_update();


--
-- Name: acteur acteur_etab_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.acteur
    ADD CONSTRAINT acteur_etab_id_fk FOREIGN KEY (acteur_etablissement_id) REFERENCES public.etablissement(id) ON DELETE CASCADE;


--
-- Name: acteur acteur_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.acteur
    ADD CONSTRAINT acteur_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: acteur acteur_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.acteur
    ADD CONSTRAINT acteur_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: acteur acteur_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.acteur
    ADD CONSTRAINT acteur_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: acteur acteur_indiv_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.acteur
    ADD CONSTRAINT acteur_indiv_fk FOREIGN KEY (individu_id) REFERENCES public.individu(id);


--
-- Name: acteur acteur_role_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.acteur
    ADD CONSTRAINT acteur_role_id_fk FOREIGN KEY (role_id) REFERENCES public.role(id);


--
-- Name: acteur acteur_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.acteur
    ADD CONSTRAINT acteur_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id) ON DELETE CASCADE;


--
-- Name: acteur acteur_these_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.acteur
    ADD CONSTRAINT acteur_these_fk FOREIGN KEY (these_id) REFERENCES public.these(id) ON DELETE CASCADE;


--
-- Name: attestation attestation_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.attestation
    ADD CONSTRAINT attestation_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: attestation attestation_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.attestation
    ADD CONSTRAINT attestation_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: attestation attestation_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.attestation
    ADD CONSTRAINT attestation_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: attestation attestation_these_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.attestation
    ADD CONSTRAINT attestation_these_fk FOREIGN KEY (these_id) REFERENCES public.these(id) ON DELETE CASCADE;


--
-- Name: soutenance_avis avis_destructeur_id; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_avis
    ADD CONSTRAINT avis_destructeur_id FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_avis avis_fichier_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_avis
    ADD CONSTRAINT avis_fichier_id_fk FOREIGN KEY (fichier_id) REFERENCES public.fichier(id);


--
-- Name: soutenance_avis avis_membre_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_avis
    ADD CONSTRAINT avis_membre_fk FOREIGN KEY (membre_id) REFERENCES public.soutenance_membre(id) ON DELETE CASCADE;


--
-- Name: soutenance_avis avis_modificateur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_avis
    ADD CONSTRAINT avis_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_avis avis_proposition_id; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_avis
    ADD CONSTRAINT avis_proposition_id FOREIGN KEY (proposition_id) REFERENCES public.soutenance_proposition(id) ON DELETE CASCADE;


--
-- Name: soutenance_avis avis_validation_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_avis
    ADD CONSTRAINT avis_validation_fk FOREIGN KEY (validation_id) REFERENCES public.validation(id);


--
-- Name: diffusion diffusion_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.diffusion
    ADD CONSTRAINT diffusion_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: diffusion diffusion_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.diffusion
    ADD CONSTRAINT diffusion_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: diffusion diffusion_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.diffusion
    ADD CONSTRAINT diffusion_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: doctorant_compl doctorant_compl_doctorant_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.doctorant_compl
    ADD CONSTRAINT doctorant_compl_doctorant_fk FOREIGN KEY (doctorant_id) REFERENCES public.doctorant(id) ON DELETE CASCADE;


--
-- Name: doctorant doctorant_etab_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.doctorant
    ADD CONSTRAINT doctorant_etab_fk FOREIGN KEY (etablissement_id) REFERENCES public.etablissement(id) ON DELETE CASCADE;


--
-- Name: doctorant doctorant_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.doctorant
    ADD CONSTRAINT doctorant_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: doctorant doctorant_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.doctorant
    ADD CONSTRAINT doctorant_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: doctorant doctorant_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.doctorant
    ADD CONSTRAINT doctorant_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: doctorant doctorant_indiv_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.doctorant
    ADD CONSTRAINT doctorant_indiv_fk FOREIGN KEY (individu_id) REFERENCES public.individu(id) ON DELETE CASCADE;


--
-- Name: doctorant doctorant_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.doctorant
    ADD CONSTRAINT doctorant_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id) ON DELETE CASCADE;


--
-- Name: ecole_doct ecole_doct_hcfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.ecole_doct
    ADD CONSTRAINT ecole_doct_hcfk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: ecole_doct ecole_doct_hdfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.ecole_doct
    ADD CONSTRAINT ecole_doct_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: ecole_doct ecole_doct_hmfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.ecole_doct
    ADD CONSTRAINT ecole_doct_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: ecole_doct ecole_doct_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.ecole_doct
    ADD CONSTRAINT ecole_doct_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id) ON DELETE CASCADE;


--
-- Name: ecole_doct ecole_doct_struct_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.ecole_doct
    ADD CONSTRAINT ecole_doct_struct_fk FOREIGN KEY (structure_id) REFERENCES public.structure(id) ON DELETE CASCADE;


--
-- Name: etablissement etab_struct_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.etablissement
    ADD CONSTRAINT etab_struct_fk FOREIGN KEY (structure_id) REFERENCES public.structure(id) ON DELETE CASCADE;


--
-- Name: etablissement etab_util_createur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.etablissement
    ADD CONSTRAINT etab_util_createur_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: etablissement etab_util_destructeur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.etablissement
    ADD CONSTRAINT etab_util_destructeur_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: etablissement etab_util_modificateur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.etablissement
    ADD CONSTRAINT etab_util_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: etablissement etablissement_fichier_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.etablissement
    ADD CONSTRAINT etablissement_fichier_id_fk FOREIGN KEY (signature_convocation_id) REFERENCES public.fichier(id) ON DELETE SET NULL;


--
-- Name: etablissement etablissement_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.etablissement
    ADD CONSTRAINT etablissement_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id) ON DELETE CASCADE;


--
-- Name: fichier fichier_hcfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.fichier
    ADD CONSTRAINT fichier_hcfk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: fichier fichier_hdfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.fichier
    ADD CONSTRAINT fichier_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: fichier fichier_hmfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.fichier
    ADD CONSTRAINT fichier_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: fichier fichier_nature_fic_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.fichier
    ADD CONSTRAINT fichier_nature_fic_id_fk FOREIGN KEY (nature_id) REFERENCES public.nature_fichier(id);


--
-- Name: fichier_these fichier_these_fichier_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.fichier_these
    ADD CONSTRAINT fichier_these_fichier_fk FOREIGN KEY (fichier_id) REFERENCES public.fichier(id) ON DELETE CASCADE;


--
-- Name: fichier_these fichier_these_these_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.fichier_these
    ADD CONSTRAINT fichier_these_these_fk FOREIGN KEY (these_id) REFERENCES public.these(id) ON DELETE CASCADE;


--
-- Name: fichier fichier_version_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.fichier
    ADD CONSTRAINT fichier_version_fk FOREIGN KEY (version_fichier_id) REFERENCES public.version_fichier(id) ON DELETE CASCADE;


--
-- Name: financement financement_hcfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.financement
    ADD CONSTRAINT financement_hcfk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: financement financement_hdfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.financement
    ADD CONSTRAINT financement_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: financement financement_hmfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.financement
    ADD CONSTRAINT financement_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: financement financement_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.financement
    ADD CONSTRAINT financement_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id);


--
-- Name: import_observ_result import_observ_result_ioe_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.import_observ_result
    ADD CONSTRAINT import_observ_result_ioe_fk FOREIGN KEY (import_observ_id) REFERENCES public.import_observ(id) ON DELETE CASCADE;


--
-- Name: individu individu_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.individu
    ADD CONSTRAINT individu_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: individu individu_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.individu
    ADD CONSTRAINT individu_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: individu individu_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.individu
    ADD CONSTRAINT individu_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: individu_role individu_role_ind_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.individu_role
    ADD CONSTRAINT individu_role_ind_id_fk FOREIGN KEY (individu_id) REFERENCES public.individu(id);


--
-- Name: individu_role individu_role_role_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.individu_role
    ADD CONSTRAINT individu_role_role_id_fk FOREIGN KEY (role_id) REFERENCES public.role(id) ON DELETE CASCADE;


--
-- Name: individu individu_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.individu
    ADD CONSTRAINT individu_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id) ON DELETE CASCADE;


--
-- Name: information information_hcfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.information
    ADD CONSTRAINT information_hcfk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: information information_hdfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.information
    ADD CONSTRAINT information_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: information information_hmfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.information
    ADD CONSTRAINT information_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: information information_langue_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.information
    ADD CONSTRAINT information_langue_id_fk FOREIGN KEY (langue_id) REFERENCES public.information_langue(id);


--
-- Name: import_obs_notif iond__io_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.import_obs_notif
    ADD CONSTRAINT iond__io_fk FOREIGN KEY (import_observ_id) REFERENCES public.import_observ(id) ON DELETE CASCADE;


--
-- Name: import_obs_notif iond__n_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.import_obs_notif
    ADD CONSTRAINT iond__n_fk FOREIGN KEY (notif_id) REFERENCES public.notif(id) ON DELETE CASCADE;


--
-- Name: import_obs_result_notif iornr__nr_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.import_obs_result_notif
    ADD CONSTRAINT iornr__nr_fk FOREIGN KEY (notif_result_id) REFERENCES public.notif_result(id) ON DELETE CASCADE;


--
-- Name: soutenance_justificatif justificatif_createur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_justificatif
    ADD CONSTRAINT justificatif_createur_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_justificatif justificatif_destructeur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_justificatif
    ADD CONSTRAINT justificatif_destructeur_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_justificatif justificatif_fichier_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_justificatif
    ADD CONSTRAINT justificatif_fichier_fk FOREIGN KEY (fichier_id) REFERENCES public.fichier_these(id) ON DELETE CASCADE;


--
-- Name: soutenance_justificatif justificatif_membre_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_justificatif
    ADD CONSTRAINT justificatif_membre_fk FOREIGN KEY (membre_id) REFERENCES public.soutenance_membre(id) ON DELETE SET NULL;


--
-- Name: soutenance_justificatif justificatif_modificateur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_justificatif
    ADD CONSTRAINT justificatif_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_justificatif justificatif_proposition_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_justificatif
    ADD CONSTRAINT justificatif_proposition_fk FOREIGN KEY (proposition_id) REFERENCES public.soutenance_proposition(id) ON DELETE CASCADE;


--
-- Name: liste_diff liste_diff_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.liste_diff
    ADD CONSTRAINT liste_diff_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: liste_diff liste_diff_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.liste_diff
    ADD CONSTRAINT liste_diff_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: liste_diff liste_diff_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.liste_diff
    ADD CONSTRAINT liste_diff_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: mail_confirmation mailconfirmation_individuid_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.mail_confirmation
    ADD CONSTRAINT mailconfirmation_individuid_fk FOREIGN KEY (individu_id) REFERENCES public.individu(id);


--
-- Name: soutenance_membre membre_createur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_membre
    ADD CONSTRAINT membre_createur_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_membre membre_destructeur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_membre
    ADD CONSTRAINT membre_destructeur_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_membre membre_modificateur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_membre
    ADD CONSTRAINT membre_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_membre membre_proposition_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_membre
    ADD CONSTRAINT membre_proposition_fk FOREIGN KEY (proposition_id) REFERENCES public.soutenance_proposition(id) ON DELETE CASCADE;


--
-- Name: soutenance_membre membre_qualite_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_membre
    ADD CONSTRAINT membre_qualite_fk FOREIGN KEY (qualite) REFERENCES public.soutenance_qualite(id) ON DELETE SET NULL;


--
-- Name: metadonnee_these metadonnee_these_these_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.metadonnee_these
    ADD CONSTRAINT metadonnee_these_these_id_fk FOREIGN KEY (these_id) REFERENCES public.these(id) ON DELETE CASCADE;


--
-- Name: diffusion mise_en_ligne_these_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.diffusion
    ADD CONSTRAINT mise_en_ligne_these_fk FOREIGN KEY (these_id) REFERENCES public.these(id) ON DELETE CASCADE;


--
-- Name: notif_result notif_result__notif_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.notif_result
    ADD CONSTRAINT notif_result__notif_fk FOREIGN KEY (notif_id) REFERENCES public.notif(id) ON DELETE CASCADE;


--
-- Name: origine_financement origine_financement_hcfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.origine_financement
    ADD CONSTRAINT origine_financement_hcfk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: origine_financement origine_financement_hdfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.origine_financement
    ADD CONSTRAINT origine_financement_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: origine_financement origine_financement_hmfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.origine_financement
    ADD CONSTRAINT origine_financement_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: origine_financement origine_financement_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.origine_financement
    ADD CONSTRAINT origine_financement_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id);


--
-- Name: profil_privilege profil_privilege_profil_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.profil_privilege
    ADD CONSTRAINT profil_privilege_profil_id_fk FOREIGN KEY (profil_id) REFERENCES public.profil(id);


--
-- Name: profil_to_role profil_to_role_profil_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.profil_to_role
    ADD CONSTRAINT profil_to_role_profil_id_fk FOREIGN KEY (profil_id) REFERENCES public.profil(id);


--
-- Name: profil_to_role profil_to_role_role_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.profil_to_role
    ADD CONSTRAINT profil_to_role_role_id_fk FOREIGN KEY (role_id) REFERENCES public.role(id);


--
-- Name: soutenance_proposition proposition_createur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_proposition
    ADD CONSTRAINT proposition_createur_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_proposition proposition_destructeur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_proposition
    ADD CONSTRAINT proposition_destructeur_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_proposition proposition_modificateur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_proposition
    ADD CONSTRAINT proposition_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_proposition proposition_these_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_proposition
    ADD CONSTRAINT proposition_these_fk FOREIGN KEY (these_id) REFERENCES public.these(id) ON DELETE CASCADE;


--
-- Name: rapport_annuel rapport_annuel_fichier_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.rapport_annuel
    ADD CONSTRAINT rapport_annuel_fichier_fk FOREIGN KEY (fichier_id) REFERENCES public.fichier(id) ON DELETE CASCADE;


--
-- Name: rapport_annuel rapport_annuel_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.rapport_annuel
    ADD CONSTRAINT rapport_annuel_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: rapport_annuel rapport_annuel_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.rapport_annuel
    ADD CONSTRAINT rapport_annuel_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: rapport_annuel rapport_annuel_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.rapport_annuel
    ADD CONSTRAINT rapport_annuel_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: rapport_annuel rapport_annuel_these_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.rapport_annuel
    ADD CONSTRAINT rapport_annuel_these_fk FOREIGN KEY (these_id) REFERENCES public.these(id) ON DELETE CASCADE;


--
-- Name: etablissement_rattach rattachement_etab_id; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.etablissement_rattach
    ADD CONSTRAINT rattachement_etab_id FOREIGN KEY (etablissement_id) REFERENCES public.etablissement(id) ON DELETE CASCADE;


--
-- Name: etablissement_rattach rattachement_unite_id; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.etablissement_rattach
    ADD CONSTRAINT rattachement_unite_id FOREIGN KEY (unite_id) REFERENCES public.unite_rech(id) ON DELETE CASCADE;


--
-- Name: rdv_bu rdv_bu_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.rdv_bu
    ADD CONSTRAINT rdv_bu_fk FOREIGN KEY (these_id) REFERENCES public.these(id) ON DELETE CASCADE;


--
-- Name: rdv_bu rdv_bu_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.rdv_bu
    ADD CONSTRAINT rdv_bu_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: rdv_bu rdv_bu_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.rdv_bu
    ADD CONSTRAINT rdv_bu_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: rdv_bu rdv_bu_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.rdv_bu
    ADD CONSTRAINT rdv_bu_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: role role_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.role
    ADD CONSTRAINT role_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: role role_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.role
    ADD CONSTRAINT role_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: role role_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.role
    ADD CONSTRAINT role_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: profil_privilege role_priv_mod_priv_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.profil_privilege
    ADD CONSTRAINT role_priv_mod_priv_id_fk FOREIGN KEY (privilege_id) REFERENCES public.privilege(id) ON DELETE CASCADE;


--
-- Name: role_privilege role_privilege_priv_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.role_privilege
    ADD CONSTRAINT role_privilege_priv_id_fk FOREIGN KEY (privilege_id) REFERENCES public.privilege(id) ON DELETE CASCADE;


--
-- Name: role_privilege role_privilege_role_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.role_privilege
    ADD CONSTRAINT role_privilege_role_id_fk FOREIGN KEY (role_id) REFERENCES public.role(id) ON DELETE CASCADE;


--
-- Name: role role_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.role
    ADD CONSTRAINT role_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id) ON DELETE CASCADE;


--
-- Name: role role_structure_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.role
    ADD CONSTRAINT role_structure_id_fk FOREIGN KEY (structure_id) REFERENCES public.structure(id) ON DELETE CASCADE;


--
-- Name: role role_type_struct_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.role
    ADD CONSTRAINT role_type_struct_id_fk FOREIGN KEY (type_structure_dependant_id) REFERENCES public.type_structure(id) ON DELETE CASCADE;


--
-- Name: profil rolemodele_structuretype_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.profil
    ADD CONSTRAINT rolemodele_structuretype_fk FOREIGN KEY (structure_type) REFERENCES public.type_structure(id);


--
-- Name: soutenance_intervention sintervention_these_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_intervention
    ADD CONSTRAINT sintervention_these_id_fk FOREIGN KEY (these_id) REFERENCES public.these(id);


--
-- Name: soutenance_intervention sintervention_userc_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_intervention
    ADD CONSTRAINT sintervention_userc_id_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_intervention sintervention_userd_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_intervention
    ADD CONSTRAINT sintervention_userd_id_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_intervention sintervention_userm_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_intervention
    ADD CONSTRAINT sintervention_userm_id_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: source source_etablissement_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.source
    ADD CONSTRAINT source_etablissement_id_fk FOREIGN KEY (etablissement_id) REFERENCES public.etablissement(id);


--
-- Name: soutenance_membre soutemembre_acteur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_membre
    ADD CONSTRAINT soutemembre_acteur_fk FOREIGN KEY (acteur_id) REFERENCES public.acteur(id) ON DELETE CASCADE;


--
-- Name: soutenance_proposition soutenance_etat_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_proposition
    ADD CONSTRAINT soutenance_etat_id_fk FOREIGN KEY (etat_id) REFERENCES public.soutenance_etat(id) ON DELETE SET NULL;


--
-- Name: soutenance_qualite_sup sqs_createur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_qualite_sup
    ADD CONSTRAINT sqs_createur_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_qualite_sup sqs_destructeur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_qualite_sup
    ADD CONSTRAINT sqs_destructeur_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_qualite_sup sqs_modificateur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_qualite_sup
    ADD CONSTRAINT sqs_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_qualite_sup sqs_qualite_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_qualite_sup
    ADD CONSTRAINT sqs_qualite_fk FOREIGN KEY (qualite_id) REFERENCES public.soutenance_qualite(id) ON DELETE CASCADE;


--
-- Name: soutenance_qualite squalite_utilisateur_id_fk_1; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_qualite
    ADD CONSTRAINT squalite_utilisateur_id_fk_1 FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_qualite squalite_utilisateur_id_fk_2; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_qualite
    ADD CONSTRAINT squalite_utilisateur_id_fk_2 FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: soutenance_qualite squalite_utilisateur_id_fk_3; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.soutenance_qualite
    ADD CONSTRAINT squalite_utilisateur_id_fk_3 FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: structure_substit str_substit_createur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.structure_substit
    ADD CONSTRAINT str_substit_createur_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: structure_substit str_substit_destructeur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.structure_substit
    ADD CONSTRAINT str_substit_destructeur_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: structure_substit str_substit_modificateur_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.structure_substit
    ADD CONSTRAINT str_substit_modificateur_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: structure_substit str_substit_str_from_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.structure_substit
    ADD CONSTRAINT str_substit_str_from_fk FOREIGN KEY (from_structure_id) REFERENCES public.structure(id) ON DELETE CASCADE;


--
-- Name: structure_substit str_substit_str_to_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.structure_substit
    ADD CONSTRAINT str_substit_str_to_fk FOREIGN KEY (to_structure_id) REFERENCES public.structure(id) ON DELETE CASCADE;


--
-- Name: structure structure_hcfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.structure
    ADD CONSTRAINT structure_hcfk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: structure structure_hdfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.structure
    ADD CONSTRAINT structure_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: structure structure_hmfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.structure
    ADD CONSTRAINT structure_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: structure structure_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.structure
    ADD CONSTRAINT structure_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id);


--
-- Name: structure structure_type_structure_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.structure
    ADD CONSTRAINT structure_type_structure_id_fk FOREIGN KEY (type_structure_id) REFERENCES public.type_structure(id) ON DELETE CASCADE;


--
-- Name: privilege sys_c006123; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.privilege
    ADD CONSTRAINT sys_c006123 FOREIGN KEY (categorie_id) REFERENCES public.categorie_privilege(id) ON DELETE CASCADE;


--
-- Name: doctorant_compl thesard_compl_hcfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.doctorant_compl
    ADD CONSTRAINT thesard_compl_hcfk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: doctorant_compl thesard_compl_hdfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.doctorant_compl
    ADD CONSTRAINT thesard_compl_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: doctorant_compl thesard_compl_hmfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.doctorant_compl
    ADD CONSTRAINT thesard_compl_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: these_annee_univ these_annee_univ_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these_annee_univ
    ADD CONSTRAINT these_annee_univ_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: these_annee_univ these_annee_univ_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these_annee_univ
    ADD CONSTRAINT these_annee_univ_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: these_annee_univ these_annee_univ_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these_annee_univ
    ADD CONSTRAINT these_annee_univ_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: these_annee_univ these_annee_univ_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these_annee_univ
    ADD CONSTRAINT these_annee_univ_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id) ON DELETE CASCADE;


--
-- Name: these_annee_univ these_annee_univ_these_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these_annee_univ
    ADD CONSTRAINT these_annee_univ_these_id_fk FOREIGN KEY (these_id) REFERENCES public.these(id) ON DELETE CASCADE;


--
-- Name: these these_doctorant_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these
    ADD CONSTRAINT these_doctorant_fk FOREIGN KEY (doctorant_id) REFERENCES public.doctorant(id) ON DELETE CASCADE;


--
-- Name: these these_ecole_doct_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these
    ADD CONSTRAINT these_ecole_doct_fk FOREIGN KEY (ecole_doct_id) REFERENCES public.ecole_doct(id) ON DELETE CASCADE;


--
-- Name: these these_etab_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these
    ADD CONSTRAINT these_etab_fk FOREIGN KEY (etablissement_id) REFERENCES public.etablissement(id) ON DELETE CASCADE;


--
-- Name: these these_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these
    ADD CONSTRAINT these_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: these these_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these
    ADD CONSTRAINT these_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: these these_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these
    ADD CONSTRAINT these_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: these these_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these
    ADD CONSTRAINT these_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id) ON DELETE CASCADE;


--
-- Name: these these_unite_rech_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.these
    ADD CONSTRAINT these_unite_rech_fk FOREIGN KEY (unite_rech_id) REFERENCES public.unite_rech(id) ON DELETE CASCADE;


--
-- Name: titre_acces titre_acces_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.titre_acces
    ADD CONSTRAINT titre_acces_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: titre_acces titre_acces_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.titre_acces
    ADD CONSTRAINT titre_acces_hd_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: titre_acces titre_acces_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.titre_acces
    ADD CONSTRAINT titre_acces_hm_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: titre_acces titre_acces_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.titre_acces
    ADD CONSTRAINT titre_acces_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id) ON DELETE CASCADE;


--
-- Name: titre_acces titre_acces_these_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.titre_acces
    ADD CONSTRAINT titre_acces_these_id_fk FOREIGN KEY (these_id) REFERENCES public.these(id) ON DELETE CASCADE;


--
-- Name: unite_rech unite_rech_compl_hcfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.unite_rech
    ADD CONSTRAINT unite_rech_compl_hcfk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: unite_rech unite_rech_compl_hdfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.unite_rech
    ADD CONSTRAINT unite_rech_compl_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: unite_rech unite_rech_compl_hmfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.unite_rech
    ADD CONSTRAINT unite_rech_compl_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: unite_rech unite_rech_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.unite_rech
    ADD CONSTRAINT unite_rech_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id) ON DELETE CASCADE;


--
-- Name: unite_rech unite_rech_struct_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.unite_rech
    ADD CONSTRAINT unite_rech_struct_fk FOREIGN KEY (structure_id) REFERENCES public.structure(id) ON DELETE CASCADE;


--
-- Name: utilisateur utilisateur_individu_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.utilisateur
    ADD CONSTRAINT utilisateur_individu_fk FOREIGN KEY (individu_id) REFERENCES public.individu(id) ON DELETE SET NULL;


--
-- Name: validation validation_hcfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.validation
    ADD CONSTRAINT validation_hcfk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: validation validation_hdfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.validation
    ADD CONSTRAINT validation_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: validation validation_hmfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.validation
    ADD CONSTRAINT validation_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: validation validation_individu_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.validation
    ADD CONSTRAINT validation_individu_id_fk FOREIGN KEY (individu_id) REFERENCES public.individu(id) ON DELETE CASCADE;


--
-- Name: validation validation_these_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.validation
    ADD CONSTRAINT validation_these_fk FOREIGN KEY (these_id) REFERENCES public.these(id) ON DELETE CASCADE;


--
-- Name: validation validation_type_validation_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.validation
    ADD CONSTRAINT validation_type_validation_fk FOREIGN KEY (type_validation_id) REFERENCES public.type_validation(id) ON DELETE CASCADE;


--
-- Name: validite_fichier validite_fichier_ffk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.validite_fichier
    ADD CONSTRAINT validite_fichier_ffk FOREIGN KEY (fichier_id) REFERENCES public.fichier(id) ON DELETE CASCADE;


--
-- Name: validite_fichier validite_fichier_hcfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.validite_fichier
    ADD CONSTRAINT validite_fichier_hcfk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id);


--
-- Name: validite_fichier validite_fichier_hdfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.validite_fichier
    ADD CONSTRAINT validite_fichier_hdfk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id);


--
-- Name: validite_fichier validite_fichier_hmfk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.validite_fichier
    ADD CONSTRAINT validite_fichier_hmfk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id);


--
-- Name: variable variable_etab_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.variable
    ADD CONSTRAINT variable_etab_fk FOREIGN KEY (etablissement_id) REFERENCES public.etablissement(id) ON DELETE CASCADE;


--
-- Name: variable variable_hc_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.variable
    ADD CONSTRAINT variable_hc_fk FOREIGN KEY (histo_createur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: variable variable_hd_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.variable
    ADD CONSTRAINT variable_hd_fk FOREIGN KEY (histo_modificateur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: variable variable_hm_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.variable
    ADD CONSTRAINT variable_hm_fk FOREIGN KEY (histo_destructeur_id) REFERENCES public.utilisateur(id) ON DELETE CASCADE;


--
-- Name: variable variable_source_fk; Type: FK CONSTRAINT; Schema: public; Owner: ad_sygal_dev
--

ALTER TABLE ONLY public.variable
    ADD CONSTRAINT variable_source_fk FOREIGN KEY (source_id) REFERENCES public.source(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

