--
-- PostgreSQL database dump
--

-- Dumped from database version 15.5 (Debian 15.5-0+deb12u1)
-- Dumped by pg_dump version 16.2 (Ubuntu 16.2-1.pgdg20.04+1)

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

--
-- Data for Name: admission_etat; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.admission_etat (code, libelle, description, icone, couleur, ordre) VALUES ('C', 'En cours de saisie', 'Dossier d''admission en cours de saisie', '', '', 1);
INSERT INTO public.admission_etat (code, libelle, description, icone, couleur, ordre) VALUES ('E', 'En cours de validation', 'Dossier d''admission en cours de validation', '', '', 2);
INSERT INTO public.admission_etat (code, libelle, description, icone, couleur, ordre) VALUES ('A', 'Abandonné', 'Dossier d''admission abandonné', '', '', 4);
INSERT INTO public.admission_etat (code, libelle, description, icone, couleur, ordre) VALUES ('R', 'Rejeté', 'Dossier d''admission rejeté', '', '', 5);
INSERT INTO public.admission_etat (code, libelle, description, icone, couleur, ordre) VALUES ('V', 'Validé', 'Dossier d''admission validé', '', '', 3);


--
-- Data for Name: admission_type_validation; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.admission_type_validation (id, code, libelle) VALUES (1, 'ATTESTATION_HONNEUR_CHARTE_DOCTORALE', 'Attestation par l''étudiant de la bonne lecture de sa charte doctorale');
INSERT INTO public.admission_type_validation (id, code, libelle) VALUES (2, 'ATTESTATION_HONNEUR', 'Attestation sur l''honneur de la part de l''étudiant');
INSERT INTO public.admission_type_validation (id, code, libelle) VALUES (3, 'VALIDATION_GESTIONNAIRE', 'Validation effectuée par les gestionnaires');
INSERT INTO public.admission_type_validation (id, code, libelle) VALUES (4, 'VALIDATION_CONVENTION_FORMATION_DOCT_DIR_THESE', 'Validation de la bonne lecture de la convention de formation doctorale par la direction de thèse');
INSERT INTO public.admission_type_validation (id, code, libelle) VALUES (5, 'VALIDATION_CONVENTION_FORMATION_DOCT_CODIR_THESE', 'Validation de la bonne lecture de la convention de formation doctorale par la co-direction de thèse');
INSERT INTO public.admission_type_validation (id, code, libelle) VALUES (6, 'VALIDATION_CONVENTION_FORMATION_DOCT_DIR_UR', 'Validation de la bonne lecture de la convention de formation doctorale par la direction de l''unité de recherche');
INSERT INTO public.admission_type_validation (id, code, libelle) VALUES (7, 'VALIDATION_CONVENTION_FORMATION_DOCT_DIR_ED', 'Validation de la bonne lecture de la convention de formation doctorale par la direction de l''école doctorale');
INSERT INTO public.admission_type_validation (id, code, libelle) VALUES (8, 'SIGNATURE_PRESIDENT', 'Signature de la présidence de l''établissement d''inscription');


--
-- Data for Name: categorie_privilege; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (3100, 'financement', 'Financement', 5);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (21, 'ecole-doctorale', 'École doctorale', 100);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (23, 'faq', 'FAQ', 10);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (1, 'droit', 'Gestion des droits', 1);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (2, 'import', 'Import', 10);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (3, 'these', 'Thèse', 20);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (4, 'doctorant', 'Doctorant', 30);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5, 'utilisateur', 'Utilisateur', 5);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (22, 'unite-recherche', 'Unité de Recherche', 200);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (24, 'validation', 'Validations', 40);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (20, 'etablissement', 'Établissement', 90);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (50, 'indicateur', 'Indicateur & Statistiques', 250);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (47, 'fichier-commun', 'Dépôt de fichier commun non lié à une thèse (ex: avenant à la convention de MEL)', 50);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (101, 'soutenance', 'Soutenance', 1000);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (102, 'co-encadrant', 'Gestion des co-encadrants', 21);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (7, 'page-information', 'Page d''information', NULL);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (3080, 'gestion-president', 'Gestion des Présidents du jury', 22);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (3121, 'rapport-activite', 'Rapports d''activité', 23);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (3140, 'rapport-csi', 'Rapports CSI', 24);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (3141, 'rapport-miparcours', 'Rapports mi-parcours', 24);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (3160, 'unicaen-auth-token', 'Jetons utilisateur', 10);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (100, 'structure', 'Structures associées à SyGAL (écoles doctorales, établissements et unités de recherche)', 80);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (3001, 'liste-diffusion', 'Liste de diffusion', 50);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (3101, 'soutenance_intervention', 'Intervention sur les soutenances', 102);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (3102, 'soutenance_justificatif', 'Justificatifs associés à la soutenance', 102);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (3200, 'unicaen-db-import', 'Module unicaen/db-import', NULL);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (3220, 'individu', 'Gestion des individus', 500);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5000, 'formation', 'Module de formation', 5000);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5001, 'formation_module', 'Gestion des modules de formations', 5100);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5002, 'formation_formation', 'Gestion des formations', 5200);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5003, 'formation_session', 'Gestion des sessions de formations', 5300);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5004, 'formation_seance', 'Gestion des séances de formations', 5400);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5005, 'formation_inscription', 'Gestion des inscriptions aux formations', 5500);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5006, 'formation_enquete', 'Gestion de l''enquête', 5600);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5020, 'documentmacro', 'UnicaenRenderer - Gestion des macros', 10000);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5021, 'documenttemplate', 'UnicaenRenderer - Gestion des templates', 10010);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5022, 'documentcontenu', 'UnicaenRenderer - Gestion des contenus', 10020);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5040, 'step-star', 'STEP-STAR', 20);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5080, 'parametrecategorie', 'UnicaenParametre - Gestion des catégories de paramètres', 100);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5081, 'parametre', 'UnicaenParametre - Gestion des paramètres', 101);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5100, 'missionenseignement', 'Gestion des missions d''enseignement', 1000);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (19, 'substitution', 'Substitutions', 300);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5120, 'admission', 'Admission', 11000);
INSERT INTO public.categorie_privilege (id, code, libelle, ordre) VALUES (5160, 'acteur', 'Acteurs des thèses', 20);


--
-- Data for Name: discipline_sise; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.discipline_sise (id, code, libelle) VALUES (1, '4200001', 'MATHEMATIQUES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (2, '4200002', 'PHYSIQUE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (3, '4200003', 'CHIMIE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (4, '4200004', 'MATHEMATIQUES APPLIQUEES ET SCIENCES SOCIALES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (5, '4200005', 'SCIENCES DE L''UNIVERS');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (6, '4200006', ' ASPECTS MOLECULAIRES ET CELLULAIRES DE LA BIOLOGIE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (7, '4200007', 'PHYSIOLOGIE ET BIOLOGIE DES ORGANISMES - POPULATIONS - INTERACTIONS');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (8, '4200008', 'RECHERCHE CLINIQUE, INNOVATION TECHNOLOGIQUE, SANTE PUBLIQUE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (9, '4200009', 'SCIENCES AGRONOMIQUES, BIOTECHNOLOGIES AGRO-ALIMENTAIRES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (10, '4200010', 'MEDECINE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (11, '4200011', 'ODONTOLOGIE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (12, '4200012', 'PHARMACIE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (13, '4200013', 'STAPS');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (14, '4200014', 'MECANIQUE DES FLUIDES, ENERGETIQUE, THERMIQUE, COMBUSTION, ACOUSTIQUE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (15, '4200015', 'MECANIQUE DES SOLIDES, GENIE MECANIQUE, PRODUCTIQUE, TRANSPORT ET GENIE CIVIL');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (16, '4200016', 'GENIE CIVIL');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (17, '4200017', 'GENIE DES PROCEDES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (18, '4200018', 'INFORMATIQUE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (19, '4200019', 'GENIE ELECTRIQUE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (20, '4200020', 'ELECTRONIQUE, MICROELECTRONIQUE, OPTIQUE ET LASERS, OPTOELECTRONIQUE MICROONDES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (21, '4200021', 'SCIENCES ET TECHNOLOGIE INDUSTRIELLES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (22, '4200022', 'SCIENCES DU LANGAGE - LINGUISTIQUE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (23, '4200023', 'LANGUES ET LITTERATURES ANCIENNES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (24, '4200024', 'LANGUES ET LITTERATURES FRANCAISES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (25, '4200025', 'LITTERATURE GENERALE ET COMPAREE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (26, '4200026', 'ARTS PLASTIQUES, MUSICOLOGIE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (27, '4200027', 'FRANCAIS, LANGUE ETRANGERE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (28, '4200028', 'LANGUES ET LITTERATURES ETRANGERES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (29, '4200029', 'LANGUES ETRANGERES APPLIQUEES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (30, '4200030', 'CULTURES ET LANGUES REGIONALES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (31, '4200031', 'PHILOSOPHIE, EPISTEMOLOGIE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (32, '4200032', 'HISTOIRE, HISTOIRE DE L’ART ET ARCHEOLOGIE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (33, '4200033', 'GEOGRAPHIE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (34, '4200034', 'AMENAGEMENT');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (35, '4200035', 'ARCHEOLOGIE, ETHNOLOGIE, PREHISTOIRE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (36, '4200036', 'SCIENCES RELIGIEUSES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (37, '4200037', 'PSYCHOLOGIE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (38, '4200038', 'SOCIOLOGIE, DEMOGRAPHIE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (39, '4200039', 'SCIENCES DE L''EDUCATION');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (40, '4200040', 'SCIENCES DE L''INFORMATION ET DE LA COMMUNICATION');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (41, '4200041', 'SCIENCES JURIDIQUES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (42, '4200042', 'SCIENCES POLITIQUES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (43, '4200043', 'SCIENCES ECONOMIQUES');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (44, '4200044', 'SCIENCES DE GESTION');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (45, '4200045', 'ADMINISTRATION ECONOMIQUE ET SOCIALE (AES)');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (46, '4200046', 'AUTOMATIQUE, SIGNAL, PRODUCTIQUE, ROBOTIQUE');
INSERT INTO public.discipline_sise (id, code, libelle) VALUES (47, '4200047', 'SCIENCES DE LA VIE ET DE LA SANTE');


--
-- Data for Name: domaine_scientifique; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.domaine_scientifique (id, libelle) VALUES (1, 'Mathématiques et leurs interactions');
INSERT INTO public.domaine_scientifique (id, libelle) VALUES (2, 'Physique');
INSERT INTO public.domaine_scientifique (id, libelle) VALUES (3, 'Sciences de la terre et de l''univers, espace');
INSERT INTO public.domaine_scientifique (id, libelle) VALUES (4, 'Chimie');
INSERT INTO public.domaine_scientifique (id, libelle) VALUES (5, 'Biologie, médecine et santé');
INSERT INTO public.domaine_scientifique (id, libelle) VALUES (6, 'Sciences humaines et humanités');
INSERT INTO public.domaine_scientifique (id, libelle) VALUES (7, 'Sciences de la société');
INSERT INTO public.domaine_scientifique (id, libelle) VALUES (8, 'Sciences pour l''ingénieur');
INSERT INTO public.domaine_scientifique (id, libelle) VALUES (9, 'Sciences et technologies de l''information et de la communication');
INSERT INTO public.domaine_scientifique (id, libelle) VALUES (10, 'Sciences agronomiques et écologiques');


--
-- Data for Name: formation_enquete_categorie; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.formation_enquete_categorie (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction) VALUES (3, 'Environnement et moyens pédagogiques', NULL, 1, 26181, '2022-11-22 08:15:15', 26181, '2022-11-22 08:15:15', NULL, NULL);
INSERT INTO public.formation_enquete_categorie (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction) VALUES (4, 'A propos de la formation', NULL, 2, 26181, '2022-11-22 08:16:02', 26181, '2022-11-22 08:16:02', NULL, NULL);
INSERT INTO public.formation_enquete_categorie (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction) VALUES (5, 'A propos du formateur', NULL, 3, 26181, '2022-11-22 08:16:14', 26181, '2022-11-22 08:16:14', NULL, NULL);
INSERT INTO public.formation_enquete_categorie (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction) VALUES (6, 'Conclusion', NULL, 4, 26181, '2022-11-22 08:16:25', 26181, '2022-11-22 08:16:25', NULL, NULL);


--
-- Data for Name: formation_enquete_question; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (3, 'Accueil général', NULL, 1, 26181, '2022-11-22 08:16:42', 26181, '2022-11-22 08:16:42', NULL, NULL, 3);
INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (4, 'Rythme de la formation', NULL, 2, 26181, '2022-11-22 08:17:01', 26181, '2022-11-22 08:17:01', NULL, NULL, 3);
INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (5, 'Qualité des équipements ou de la visio-conférence', NULL, 3, 26181, '2022-11-22 08:17:12', 26181, '2022-11-22 08:17:12', NULL, NULL, 3);
INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (6, 'Support de cours', NULL, 4, 26181, '2022-11-22 08:17:23', 26181, '2022-11-22 08:17:23', NULL, NULL, 3);
INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (7, 'Information préalable sur le contenu', NULL, 1, 26181, '2022-11-22 08:17:37', 26181, '2022-11-22 08:17:37', NULL, NULL, 4);
INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (8, 'Durée de la formation', NULL, 2, 26181, '2022-11-22 08:17:49', 26181, '2022-11-22 08:17:49', NULL, NULL, 4);
INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (9, 'Homogénéité du groupe', NULL, 3, 26181, '2022-11-22 08:18:01', 26181, '2022-11-22 08:18:01', NULL, NULL, 4);
INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (11, 'Contenu adapté au format (présentiel / distanciel)', NULL, 5, 26181, '2022-11-22 08:18:23', 26181, '2022-11-22 08:18:23', NULL, NULL, 4);
INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (14, 'Favorable aux échanges et à la participation', NULL, 3, 26181, '2022-11-22 08:18:58', 26181, '2022-11-22 08:19:24', NULL, NULL, 5);
INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (15, 'Satisfaction générale', NULL, 1, 26181, '2022-11-22 08:19:40', 26181, '2022-11-22 08:19:40', NULL, NULL, 6);
INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (13, 'Démarche pédagogique', NULL, 2, 26181, '2022-11-22 08:18:47', 26181, '2023-09-26 10:58:23', NULL, NULL, 5);
INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (10, 'Contenu adapté à mon niveau/besoin', NULL, 4, 26181, '2022-11-22 08:18:12', 26181, '2023-09-26 10:59:07', NULL, NULL, 4);
INSERT INTO public.formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction, categorie_id) VALUES (12, 'Qualités des contenus', NULL, 1, 26181, '2022-11-22 08:18:35', 26181, '2023-09-26 10:59:53', NULL, NULL, 5);


--
-- Data for Name: formation_etat; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.formation_etat (code, libelle, description, icone, couleur, ordre) VALUES ('C', 'Close', 'Formation close', 'icon icon-checked', 'darkgreen', 5);
INSERT INTO public.formation_etat (code, libelle, description, icone, couleur, ordre) VALUES ('P', 'En préparation', 'Formation en cours de préparation', 'icon icon-editer', 'purple', 1);
INSERT INTO public.formation_etat (code, libelle, description, icone, couleur, ordre) VALUES ('A', 'Session annulée', 'La session a été annulée', 'icon icon-historiser', 'darkred', 6);
INSERT INTO public.formation_etat (code, libelle, description, icone, couleur, ordre) VALUES ('I', 'Session imminente', 'Session imminente', 'icon icon-calendrier', '#FECACA', 4);
INSERT INTO public.formation_etat (code, libelle, description, icone, couleur, ordre) VALUES ('O', 'Inscription ouverte', NULL, 'icon icon-user-add', 'cadetblue', 2);
INSERT INTO public.formation_etat (code, libelle, description, icone, couleur, ordre) VALUES ('F', 'Inscription fermée', NULL, 'icon icon-user-checked', 'teal', 3);


--
-- Data for Name: import_observ; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.import_observ (id, code, table_name, column_name, operation, to_value, description, enabled, filter) VALUES (6, 'RESULTAT_PASSE_A_ADMIS', 'THESE', 'RESULTAT', 'UPDATE', '1', 'Le résultat de la thèse passe à 1 (admis)', true, 'ETABLISSEMENT_ID IN (SELECT ID FROM ETABLISSEMENT WHERE SOURCE_CODE in (''UCN'',''URN'',''ULHN'',''INSA''))');
INSERT INTO public.import_observ (id, code, table_name, column_name, operation, to_value, description, enabled, filter) VALUES (9, 'CORRECTION_PASSE_A_FACULTATIVE', 'THESE', 'CORREC_AUTORISEE', 'UPDATE', 'facultative', 'Correction attendue passe à facultative', true, 'ETABLISSEMENT_ID IN (SELECT ID FROM ETABLISSEMENT WHERE SOURCE_CODE in (''UCN'',''URN'',''ULHN'',''INSA''))');
INSERT INTO public.import_observ (id, code, table_name, column_name, operation, to_value, description, enabled, filter) VALUES (10, 'CORRECTION_PASSE_A_OBLIGATOIRE', 'THESE', 'CORREC_AUTORISEE', 'UPDATE', 'obligatoire', 'Correction attendue passe à obligatoire', true, 'ETABLISSEMENT_ID IN (SELECT ID FROM ETABLISSEMENT WHERE SOURCE_CODE in (''UCN'',''URN'',''ULHN'',''INSA''))');


--
-- Data for Name: information_langue; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.information_langue (id, libelle, drapeau) VALUES ('FR', 'Français', 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz4KPHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI5MDAiIGhlaWdodD0iNjAwIj48cmVjdCB3aWR0aD0iOTAwIiBoZWlnaHQ9IjYwMCIgZmlsbD0iI0VEMjkzOSIvPjxyZWN0IHdpZHRoPSI2MDAiIGhlaWdodD0iNjAwIiBmaWxsPSIjZmZmIi8+PHJlY3Qgd2lkdGg9IjMwMCIgaGVpZ2h0PSI2MDAiIGZpbGw9IiMwMDIzOTUiLz48L3N2Zz4K');
INSERT INTO public.information_langue (id, libelle, drapeau) VALUES ('EN', 'English', 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIj8+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB2aWV3Qm94PSIwIDAgNjAgMzAiIHdpZHRoPSIxMjAwIiBoZWlnaHQ9IjYwMCI+CjxjbGlwUGF0aCBpZD0icyI+Cgk8cGF0aCBkPSJNMCwwIHYzMCBoNjAgdi0zMCB6Ii8+CjwvY2xpcFBhdGg+CjxjbGlwUGF0aCBpZD0idCI+Cgk8cGF0aCBkPSJNMzAsMTUgaDMwIHYxNSB6IHYxNSBoLTMwIHogaC0zMCB2LTE1IHogdi0xNSBoMzAgeiIvPgo8L2NsaXBQYXRoPgo8ZyBjbGlwLXBhdGg9InVybCgjcykiPgoJPHBhdGggZD0iTTAsMCB2MzAgaDYwIHYtMzAgeiIgZmlsbD0iIzAxMjE2OSIvPgoJPHBhdGggZD0iTTAsMCBMNjAsMzAgTTYwLDAgTDAsMzAiIHN0cm9rZT0iI2ZmZiIgc3Ryb2tlLXdpZHRoPSI2Ii8+Cgk8cGF0aCBkPSJNMCwwIEw2MCwzMCBNNjAsMCBMMCwzMCIgY2xpcC1wYXRoPSJ1cmwoI3QpIiBzdHJva2U9IiNDODEwMkUiIHN0cm9rZS13aWR0aD0iNCIvPgoJPHBhdGggZD0iTTMwLDAgdjMwIE0wLDE1IGg2MCIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjEwIi8+Cgk8cGF0aCBkPSJNMzAsMCB2MzAgTTAsMTUgaDYwIiBzdHJva2U9IiNDODEwMkUiIHN0cm9rZS13aWR0aD0iNiIvPgo8L2c+Cjwvc3ZnPgo=');


--
-- Data for Name: nature_fichier; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.nature_fichier (id, code, libelle) VALUES (1, 'THESE_PDF', 'Thèse au format PDF');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (2, 'FICHIER_NON_PDF', 'Fichier non PDF joint à une thèse (ex: vidéo)');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (3, 'PV_SOUTENANCE', 'PV de soutenance');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (4, 'RAPPORT_SOUTENANCE', 'Rapport de soutenance');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (5, 'DEMANDE_CONFIDENT', 'Demande de confidentialité');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (6, 'PROLONG_CONFIDENT', 'Demande de prolongation de confidentialité');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (7, 'PRE_RAPPORT_SOUTENANCE', 'Pré-rapport de soutenance');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (8, 'CONV_MISE_EN_LIGNE', 'Convention de mise en ligne');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (22, 'COMMUNS', 'Fichier commun non lié à une thèse (ex: modèle d''avenant à la convention de MEL)');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (61, 'JUSTIFICATIF_HDR', 'Justificatif d''habilitation à diriger des recherches');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (62, 'DELOCALISATION_SOUTENANCE', 'Formulaire de délocalisation de soutenance');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (63, 'DELEGUATION_SIGNATURE', 'Formulaire de délégation de signature du rapport de soutenance (visioconférence)');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (64, 'DEMANDE_LABEL_EUROPEEN', 'Formulaire de demande de label européen');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (65, 'DEMANDE_LANGUE_ANGLAISE', 'Formulaire de demande de manuscrit ou de soutenance en anglais');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (66, 'JUSTIFICATIF_EMERITAT', 'Justificatif d''émeritat');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (67, 'AUTRES_JUSTIFICATIFS', 'Autres justificatifs concernant la soutenance');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (121, 'RAPPORT_CSI', 'Rapport CSI');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (122, 'RAPPORT_MIPARCOURS', 'Rapport mi-parcours');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (81, 'SIGNATURE_CONVOCATION', 'Signature pour la convocation à la soutenance');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (18, 'JUSTIFICATIF_ETRANGER', 'Justificatif de la qualité d''un membre de jury étranger');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (41, 'RAPPORT_ACTIVITE', 'Rapport annuel');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (141, 'SIGNATURE_RAPPORT_ACTIVITE', 'Signature figurant sur la page de validation d''un rapport d''activité');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (181, 'SIGNATURE_FORMATION', 'Signature pour les formations');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (201, 'CONV_FORMATION_DOCTORALE', 'Convention de formation doctorale');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (202, 'CONV_FORMATION_DOCTORALE_AVENANT', 'Avenant à la convention de formation doctorale');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (203, 'CHARTE_DOCTORAT', 'Charte du doctorat');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (204, 'CHARTE_DOCTORAT_AVENANT', 'Avenant à la charte du doctorat');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (9, 'CONV_MISE_EN_LIGNE_AVENANT', 'Avenant à la convention de mise en ligne');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (205, 'FORMATION_INTEGRITE_SCIENTIFIQUE', 'Justificatif de suivi de la formation "Intégrité scientifique"');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (206, 'AUTORISATION_SOUTENANCE', ' ''Autorisation de soutenance''');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (207, 'ADMISSION_DIPLOME_BAC', 'Diplôme de Bac + 5 permettant l''accès au doctorat');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (208, 'ADMISSION_CURRICULUM_VITAE', 'Curriculum Vitae');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (209, 'ADMISSION_FINANCEMENT', 'Justificatif du financement (contrat, attestation de l''employeur)');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (210, 'ADMISSION_PROJET_THESE', 'Le projet de thèse et son titre');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (211, 'ADMISSION_CONVENTION', 'Convention de formation doctorale');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (212, 'ADMISSION_CHARTE_DOCTORAT', 'Charte du doctorat');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (213, 'ADMISSION_DIPLOMES_RELEVES_TRADUITS', 'Diplômes et relevés de notes traduits en français avec tampons originaux');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (214, 'ADMISSION_ACTE_NAISSANCE', 'Extrait d''acte de naissance');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (215, 'ADMISSION_PASSEPORT', 'Photocopie du passeport (ou de la carte d''identité pour les ressortissants européens)');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (216, 'ADMISSION_DIPLOMES_TRAVAUX_EXPERIENCE_PRO', 'Diplômes, travaux et expérience professionnelle détaillés');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (217, 'ADMISSION_DEMANDE_COTUTELLE', 'Formulaire de demande de cotutelle');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (218, 'ADMISSION_DEMANDE_COENCADREMENT', 'Formulaire de demande de co-encadrement');
INSERT INTO public.nature_fichier (id, code, libelle) VALUES (219, 'ADMISSION_RECAPITULATIF_DOSSIER_SIGNE', 'Récapitulatif du dossier d''admission signé par la direction de l''établissement');


--
-- Data for Name: notif; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.notif (id, code, description, recipients, template, enabled) VALUES (21, 'notif-depot-these', 'Notification lorsqu''un fichier de thèse est téléversé', NULL, '<p>
    Bonjour,
</p>
<p>
    Ceci est un mail envoyé automatiquement par l''application <?php echo $appName ?>.
</p>
<p>
    Vous êtes informé-e que <em><?php echo $version->toString() ?></em> de la thèse de <?php echo $these->getDoctorant() ?> vient d''être déposée.
</p>
<p>
    Cliquez sur <a href="<?php echo $url ?>">ce lien</a> pour accéder à la page correspondante de l''application.
</p>
', 1);


--
-- Data for Name: pays; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (3, '248', 'ALA', 'AX', 'Îles Åland', 'ÅLAND, ÎLES', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::248');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (9, '660', 'AIA', 'AI', 'Anguilla', 'ANGUILLA', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::660');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (10, '010', 'ATA', 'AQ', 'Antarctique', 'ANTARCTIQUE', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::010');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (27, '060', 'BMU', 'BM', 'Bermudes', 'BERMUDES', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::060');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (30, '535', 'BES', 'BQ', 'Pays-Bas caribéens', 'BONAIRE, SAINT-EUSTACHE ET SABA', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::535');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (33, '074', 'BVT', 'BV', 'Île Bouvet', 'BOUVET, ÎLE', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::074');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (39, '136', 'CYM', 'KY', 'Îles Caïmans', 'CAÏMANES, ÎLES', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::136');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (47, '162', 'CXR', 'CX', 'Île Christmas', 'CHRISTMAS, ÎLE', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::162');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (49, '166', 'CCK', 'CC', 'Îles Cocos', 'COCOS (KEELING), ÎLES', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::166');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (54, '184', 'COK', 'CK', 'Îles Cook', 'COOK, ÎLES', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::184');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (61, '531', 'CUW', 'CW', 'Curaçao', 'CURAÇAO', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::531');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (75, '238', 'FLK', 'FK', 'Malouines', 'FALKLAND, ÎLES (MALVINAS)', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::238');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (76, '234', 'FRO', 'FO', 'Îles Féroé', 'FÉROÉ, ÎLES', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::234');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (83, '239', 'SGS', 'GS', 'Géorgie du Sud-et-les îles Sandwich du Sud', 'GÉORGIE DU SUD ET LES ÎLES SANDWICH DU SUD', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::239');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (89, '312', 'GLP', 'GP', 'Guadeloupe', 'GUADELOUPE', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::312');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (90, '316', 'GUM', 'GU', 'Guam', 'GUAM', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::316');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (92, '831', 'GGY', 'GG', 'Guernesey', 'GUERNESEY', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::831');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (97, '254', 'GUF', 'GF', 'Guyane', 'GUYANE FRANÇAISE', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::254');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (99, '334', 'HMD', 'HM', 'Îles Heard-et-MacDonald', 'HEARD ET MACDONALD, ÎLES', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::334');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (101, '344', 'HKG', 'HK', 'Hong Kong', 'HONG KONG', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::344');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (103, '833', 'IMN', 'IM', 'Île de Man', 'ÎLE DE MAN', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::833');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (104, '581', 'UMI', 'UM', 'Îles mineures éloignées des États-Unis', 'ÎLES MINEURES ÉLOIGNÉES DES ÉTATS-UNIS', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::581');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (105, '092', 'VGB', 'VG', 'Îles Vierges britanniques', 'ÎLES VIERGES BRITANNIQUES', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::092');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (106, '850', 'VIR', 'VI', 'Îles Vierges des États-Unis', 'ÎLES VIERGES DES ÉTATS-UNIS', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::850');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (117, '832', 'JEY', 'JE', 'Jersey', 'JERSEY', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::832');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (133, '446', 'MAC', 'MO', 'Macao', 'MACAO', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::446');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (141, '580', 'MNP', 'MP', 'Îles Mariannes du Nord', 'MARIANNES DU NORD, ÎLES', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::580');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (144, '474', 'MTQ', 'MQ', 'Martinique', 'MARTINIQUE', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::474');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (147, '175', 'MYT', 'YT', 'Mayotte', 'MAYOTTE', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::175');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (154, '500', 'MSR', 'MS', 'Montserrat', 'MONTSERRAT', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::500');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (163, '570', 'NIU', 'NU', 'Niue', 'NIUÉ', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::570');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (164, '574', 'NFK', 'NF', 'Île Norfolk', 'NORFOLK, ÎLE', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::574');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (166, '540', 'NCL', 'NC', 'Nouvelle-Calédonie', 'NOUVELLE-CALÉDONIE', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::540');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (178, '528', 'NLD', 'NL', 'Pays-Bas', 'PAYS-BAS', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::528');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (183, '258', 'PYF', 'PF', 'Polynésie française', 'POLYNÉSIE FRANÇAISE', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::258');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (187, '638', 'REU', 'RE', 'La Réunion', 'RÉUNION', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::638');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (193, '652', 'BLM', 'BL', 'Saint-Barthélemy', 'SAINT-BARTHÉLEMY', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::652');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (196, '663', 'MAF', 'MF', 'Saint-Martin', 'SAINT-MARTIN (PARTIE FRANÇAISE)', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::663');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (197, '534', 'SXM', 'SX', 'Saint-Martin', 'SAINT-MARTIN (PARTIE NÉERLANDAISE)', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::534');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (198, '666', 'SPM', 'PM', 'Saint-Pierre-et-Miquelon', 'SAINT-PIERRE-ET-MIQUELON', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::666');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (205, '016', 'ASM', 'AS', 'Samoa américaines', 'SAMOA AMÉRICAINES', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::016');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (216, '728', 'SSD', 'SS', 'Soudan du Sud', 'SOUDAN DU SUD', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::728');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (221, '744', 'SJM', 'SJ', 'Svalbard et ile Jan Mayen', 'SVALBARD ET ÎLE JAN MAYEN', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::744');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (229, '260', 'ATF', 'TF', 'Terres australes et antarctiques françaises', 'TERRES AUSTRALES FRANÇAISES', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::260');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (233, '772', 'TKL', 'TK', 'Tokelau', 'TOKELAU', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::772');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (238, '796', 'TCA', 'TC', 'Îles Turques-et-Caïques', 'TURKS ET CAÏQUES, ÎLES', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::796');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (246, '876', 'WLF', 'WF', 'Wallis-et-Futuna', 'WALLIS-ET-FUTUNA', NULL, NULL, '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::876');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (79, '250', 'FRA', 'FR', 'France', 'FRANCE', 'Français(e)', '100', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::250');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (62, '208', 'DNK', 'DK', 'Danemark', 'DANEMARK', 'Danois(e)', '101', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::208');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (112, '352', 'ISL', 'IS', 'Islande', 'ISLANDE', 'Islandais(e)', '102', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::352');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (165, '578', 'NOR', 'NO', 'Norvège', 'NORVÈGE', 'Norvegien(ne)', '103', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::578');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (218, '752', 'SWE', 'SE', 'Suède', 'SUÈDE', 'Suedois(e)', '104', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::752');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (78, '246', 'FIN', 'FI', 'Finlande', 'FINLANDE', 'Finlandais(e)', '105', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::246');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (72, '233', 'EST', 'EE', 'Estonie', 'ESTONIE', 'Estonien(ne)', '106', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::233');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (126, '428', 'LVA', 'LV', 'Lettonie', 'LETTONIE', 'Lettonien(ne)', '107', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::428');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (131, '440', 'LTU', 'LT', 'Lituanie', 'LITUANIE', 'Lithuanien(ne)', '108', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::440');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (6, '276', 'DEU', 'DE', 'Allemagne', 'ALLEMAGNE', 'Allemand(e)', '109', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::276');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (17, '040', 'AUT', 'AT', 'Autriche', 'AUTRICHE', 'Autrichien(ne)', '110', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::040');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (36, '100', 'BGR', 'BG', 'Bulgarie', 'BULGARIE', 'Bulgare', '111', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::100');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (208, '688', 'SRB', 'RS', 'Serbie', 'SERBIE', 'Serbe', '121', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::688');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (102, '348', 'HUN', 'HU', 'Hongrie', 'HONGRIE', 'Hongrois(e)', '112', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::348');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (130, '438', 'LIE', 'LI', 'Liechtenstein', 'LIECHTENSTEIN', 'Liechtenstein', '113', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::438');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (188, '642', 'ROU', 'RO', 'Roumanie', 'ROUMANIE', 'Roumain(e)', '114', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::642');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (228, '203', 'CZE', 'CZ', 'Tchéquie', 'TCHÉQUIE', 'Tcheque', '116', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::203');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (212, '703', 'SVK', 'SK', 'Slovaquie', 'SLOVAQUIE', 'Slovaque', '117', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::703');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (31, '070', 'BIH', 'BA', 'Bosnie-Herzégovine', 'BOSNIE-HERZÉGOVINE', 'Bosniaque', '118', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::070');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (59, '191', 'HRV', 'HR', 'Croatie', 'CROATIE', 'Croate', '119', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::191');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (182, '616', 'POL', 'PL', 'Pologne', 'POLOGNE', 'Polonais(e)', '122', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::616');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (190, '643', 'RUS', 'RU', 'Russie', 'RUSSIE, FÉDÉRATION DE', 'Russe', '123', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::643');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (4, '008', 'ALB', 'AL', 'Albanie', 'ALBANIE', 'Albanais(e)', '125', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::008');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (86, '300', 'GRC', 'GR', 'Grèce', 'GRÈCE', 'Grec(Que)', '126', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::300');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (114, '380', 'ITA', 'IT', 'Italie', 'ITALIE', 'Italien(ne)', '127', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::380');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (195, '674', 'SMR', 'SM', 'Saint-Marin', 'SAINT-MARIN', 'Saint Marin', '128', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::674');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (199, '336', 'VAT', 'VA', 'Saint-Siège (État de la Cité du Vatican)', 'SAINT-SIÈGE (ÉTAT DE LA CITÉ DU VATICAN)', 'Vatican(e)', '129', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::336');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (7, '020', 'AND', 'AD', 'Andorre', 'ANDORRE', 'Andorran(ne)', '130', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::020');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (24, '056', 'BEL', 'BE', 'Belgique', 'BELGIQUE', 'Belge', '131', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::056');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (189, '826', 'GBR', 'GB', 'Royaume-Uni', 'ROYAUME-UNI', 'Britannique', '132', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::826');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (71, '724', 'ESP', 'ES', 'Espagne', 'ESPAGNE', 'Espagnol(e)', '134', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::724');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (15, '533', 'ABW', 'AW', 'Aruba', 'ARUBA', 'Neerlandais(e)', '135', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::533');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (111, '372', 'IRL', 'IE', 'Irlande', 'IRLANDE', 'Irlandais(e)', '136', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::372');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (132, '442', 'LUX', 'LU', 'Luxembourg', 'LUXEMBOURG', 'Luxembourgeois(e)', '137', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::442');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (151, '492', 'MCO', 'MC', 'Monaco', 'MONACO', 'Monegasque', '138', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::492');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (185, '620', 'PRT', 'PT', 'Portugal', 'PORTUGAL', 'Portugais(e)', '139', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::620');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (219, '756', 'CHE', 'CH', 'Suisse', 'SUISSE', 'Suisse', '140', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::756');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (140, '470', 'MLT', 'MT', 'Malte', 'MALTE', 'Maltais(e)', '144', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::470');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (213, '705', 'SVN', 'SI', 'Slovénie', 'SLOVÉNIE', 'Slovene', '145', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::705');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (23, '112', 'BLR', 'BY', 'Biélorussie', 'BÉLARUS', 'Bielorusse', '148', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::112');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (150, '498', 'MDA', 'MD', 'Moldavie', 'MOLDAVIE', 'Moldave', '151', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::498');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (241, '804', 'UKR', 'UA', 'Ukraine', 'UKRAINE', 'Ukrainien(ne)', '155', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::804');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (12, '682', 'SAU', 'SA', 'Arabie saoudite', 'ARABIE SAOUDITE', 'Saoudien(ne)', '201', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::682');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (110, '368', 'IRQ', 'IQ', 'Irak', 'IRAQ', 'Irakien(ne)', '203', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::368');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (109, '364', 'IRN', 'IR', 'Iran', 'IRAN, RÉPUBLIQUE ISLAMIQUE D''', 'Iranien(ne)', '204', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::364');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (127, '422', 'LBN', 'LB', 'Liban', 'LIBAN', 'Libanais(e)', '205', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::422');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (223, '760', 'SYR', 'SY', 'Syrie', 'SYRIENNE, RÉPUBLIQUE ARABE', 'Syrien(ne)', '206', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::760');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (113, '376', 'ISR', 'IL', 'Israël', 'ISRAËL', 'Israelien(ne)', '207', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::376');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (239, '792', 'TUR', 'TR', 'Turquie', 'TURQUIE', 'Turc (Turque)', '208', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::792');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (1, '004', 'AFG', 'AF', 'Afghanistan', 'AFGHANISTAN', 'Afghan(e)', '212', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::004');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (172, '586', 'PAK', 'PK', 'Pakistan', 'PAKISTAN', 'Pakistanais(e)', '213', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::586');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (28, '064', 'BTN', 'BT', 'Bhoutan', 'BHOUTAN', 'Bhoutan', '214', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::064');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (159, '524', 'NPL', 'NP', 'Népal', 'NÉPAL', 'Nepalais(e)', '215', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::524');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (46, '156', 'CHN', 'CN', 'Chine', 'CHINE', 'Chinois(e)', '216', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::156');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (116, '392', 'JPN', 'JP', 'Japon', 'JAPON', 'Japonais(e)', '217', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::392');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (230, '764', 'THA', 'TH', 'Thaïlande', 'THAÏLANDE', 'Thailandais(e)', '219', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::764');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (180, '608', 'PHL', 'PH', 'Philippines', 'PHILIPPINES', 'Philippin(ne)', '220', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::608');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (118, '400', 'JOR', 'JO', 'Jordanie', 'JORDANIE', 'Jordanien(ne)', '222', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::400');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (107, '356', 'IND', 'IN', 'Inde', 'INDE', 'Indien(ne)', '223', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::356');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (156, '104', 'MMR', 'MM', 'Birmanie', 'MYANMAR', 'Birman(e)', '224', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::104');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (35, '096', 'BRN', 'BN', 'Brunei', 'BRUNÉI DARUSSALAM', 'Brunei', '225', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::096');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (211, '702', 'SGP', 'SG', 'Singapour', 'SINGAPOUR', 'Singapourien(ne)', '226', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::702');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (136, '458', 'MYS', 'MY', 'Malaisie', 'MALAISIE', 'Malais(e)', '227', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::458');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (138, '462', 'MDV', 'MV', 'Maldives', 'MALDIVES', 'Maldives', '229', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::462');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (108, '360', 'IDN', 'ID', 'Indonésie', 'INDONÉSIE', 'Indonesien(ne)', '231', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::360');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (40, '116', 'KHM', 'KH', 'Cambodge', 'CAMBODGE', 'Cambodgien(ne)', '234', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::116');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (217, '144', 'LKA', 'LK', 'Sri Lanka', 'SRI LANKA', 'Sri Lankais(e)', '235', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::144');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (225, '158', 'TWN', 'TW', 'Taïwan / (République de Chine (Taïwan))', 'TAÏWAN', 'Chinois(e) Taiwan', '236', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::158');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (56, '408', 'PRK', 'KP', 'Corée du Nord', 'CORÉE, RÉPUBLIQUE POPULAIRE DÉMOCRATIQUE DE', 'Nord Coreen(ne)', '238', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::408');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (55, '410', 'KOR', 'KR', 'Corée du Sud', 'CORÉE, RÉPUBLIQUE DE', 'Sud Coreen(ne)', '239', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::410');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (123, '414', 'KWT', 'KW', 'Koweït', 'KOWEÏT', 'Koweitien(ne)', '240', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::414');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (124, '418', 'LAO', 'LA', 'Laos', 'LAO, RÉPUBLIQUE DÉMOCRATIQUE POPULAIRE', 'Laotien(ne)', '241', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::418');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (152, '496', 'MNG', 'MN', 'Mongolie', 'MONGOLIE', 'Mongol(e)', '242', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::496');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (245, '704', 'VNM', 'VN', 'Viêt Nam', 'VIET NAM', 'Vietnamien(ne)', '243', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::704');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (21, '050', 'BGD', 'BD', 'Bangladesh', 'BANGLADESH', 'Bengali(e)', '246', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::050');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (68, '784', 'ARE', 'AE', 'Émirats arabes unis', 'ÉMIRATS ARABES UNIS', 'Emirats Arabes Unis', '247', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::784');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (186, '634', 'QAT', 'QA', 'Qatar', 'QATAR', 'Qatari(e)', '248', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::634');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (20, '048', 'BHR', 'BH', 'Bahreïn', 'BAHREÏN', 'Barheinien(ne)', '249', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::048');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (169, '512', 'OMN', 'OM', 'Oman', 'OMAN', 'Omanais(e)', '250', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::512');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (247, '887', 'YEM', 'YE', 'Yémen', 'YÉMEN', 'Yemenite', '251', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::887');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (14, '051', 'ARM', 'AM', 'Arménie', 'ARMÉNIE', 'Armenien(e)', '252', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::051');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (18, '031', 'AZE', 'AZ', 'Azerbaïdjan', 'AZERBAÏDJAN', 'Azeri(e)', '253', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::031');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (48, '196', 'CYP', 'CY', 'Chypre', 'CHYPRE', 'Chypriote', '254', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::196');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (82, '268', 'GEO', 'GE', 'Géorgie', 'GÉORGIE', 'Georgien(ne)', '255', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::268');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (119, '398', 'KAZ', 'KZ', 'Kazakhstan', 'KAZAKHSTAN', 'Kazakh', '256', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::398');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (121, '417', 'KGZ', 'KG', 'Kirghizistan', 'KIRGHIZISTAN', 'Kirghizistanais(e)', '257', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::417');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (171, '860', 'UZB', 'UZ', 'Ouzbékistan', 'OUZBÉKISTAN', 'Ouzbek', '258', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::860');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (224, '762', 'TJK', 'TJ', 'Tadjikistan', 'TADJIKISTAN', 'Tadjik', '259', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::762');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (237, '795', 'TKM', 'TM', 'Turkménistan', 'TURKMÉNISTAN', 'Turkmene', '260', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::795');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (66, '818', 'EGY', 'EG', 'Égypte', 'ÉGYPTE', 'Egyptien(ne)', '301', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::818');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (128, '430', 'LBR', 'LR', 'Liberia', 'LIBÉRIA', 'Liberian(e)', '302', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::430');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (2, '710', 'ZAF', 'ZA', 'Afrique du Sud', 'AFRIQUE DU SUD', 'Sud Africain(e)', '303', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::710');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (81, '270', 'GMB', 'GM', 'Gambie', 'GAMBIE', 'Gambien(ne)', '304', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::270');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (226, '834', 'TZA', 'TZ', 'Tanzanie', 'TANZANIE, RÉPUBLIQUE UNIE DE', 'Tanzanien(ne)', '309', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::834');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (249, '716', 'ZWE', 'ZW', 'Zimbabwe', 'ZIMBABWE', 'Zimbabweien(ne)', '310', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::716');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (157, '516', 'NAM', 'NA', 'Namibie', 'NAMIBIE', 'Namibien(ne)', '311', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::516');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (53, '180', 'COD', 'CD', 'République démocratique du Congo', 'CONGO, RÉPUBLIQUE DÉMOCRATIQUE DU', 'Zairois(e)', '312', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::180');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (95, '226', 'GNQ', 'GQ', 'Guinée équatoriale', 'GUINÉE ÉQUATORIALE', 'Guineen(ne) Equatori', '314', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::226');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (74, '231', 'ETH', 'ET', 'Éthiopie', 'ÉTHIOPIE', 'Ethiopien(ne)', '315', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::231');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (129, '434', 'LBY', 'LY', 'Libye', 'LIBYE', 'Libyen(ne)', '316', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::434');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (214, '706', 'SOM', 'SO', 'Somalie', 'SOMALIE', 'Somalien(ne)', '318', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::706');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (38, '108', 'BDI', 'BI', 'Burundi', 'BURUNDI', 'Burundais(e)', '321', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::108');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (41, '120', 'CMR', 'CM', 'Cameroun', 'CAMEROUN', 'Camerounais(e)', '322', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::120');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (44, '140', 'CAF', 'CF', 'République centrafricaine', 'CENTRAFRICAINE, RÉPUBLIQUE', 'Centrafricain(e)', '323', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::140');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (52, '178', 'COG', 'CG', 'République du Congo', 'CONGO', 'Congolais(e)', '324', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::178');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (58, '384', 'CIV', 'CI', 'Côte d''Ivoire', 'CÔTE D''IVOIRE', 'Ivoirien(ne)', '326', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::384');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (26, '204', 'BEN', 'BJ', 'Bénin', 'BÉNIN', 'Beninois(e)', '327', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::204');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (80, '266', 'GAB', 'GA', 'Gabon', 'GABON', 'Gabonais(e)', '328', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::266');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (84, '288', 'GHA', 'GH', 'Ghana', 'GHANA', 'Ghaneen(ne)', '329', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::288');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (93, '324', 'GIN', 'GN', 'Guinée', 'GUINÉE', 'Guineen(ne)', '330', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::324');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (37, '854', 'BFA', 'BF', 'Burkina Faso', 'BURKINA FASO', 'Burkinabe', '331', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::854');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (120, '404', 'KEN', 'KE', 'Kenya', 'KENYA', 'Kenyan(ne)', '332', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::404');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (135, '450', 'MDG', 'MG', 'Madagascar', 'MADAGASCAR', 'Malgache', '333', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::450');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (137, '454', 'MWI', 'MW', 'Malawi', 'MALAWI', 'Malawien(ne)', '334', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::454');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (139, '466', 'MLI', 'ML', 'Mali', 'MALI', 'Malien(ne)', '335', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::466');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (146, '478', 'MRT', 'MR', 'Mauritanie', 'MAURITANIE', 'Mauritanien(ne)', '336', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::478');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (161, '562', 'NER', 'NE', 'Niger', 'NIGER', 'Nigerien(ne)', '337', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::562');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (162, '566', 'NGA', 'NG', 'Nigeria', 'NIGÉRIA', 'Nigerian(e)', '338', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::566');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (170, '800', 'UGA', 'UG', 'Ouganda', 'OUGANDA', 'Ougandais(e)', '339', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::800');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (191, '646', 'RWA', 'RW', 'Rwanda', 'RWANDA', 'Ruandais(e)', '340', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::646');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (207, '686', 'SEN', 'SN', 'Sénégal', 'SÉNÉGAL', 'Senegalais(e)', '341', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::686');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (210, '694', 'SLE', 'SL', 'Sierra Leone', 'SIERRA LEONE', 'Sierra Leone', '342', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::694');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (215, '729', 'SDN', 'SD', 'Soudan', 'SOUDAN', 'Soudanais(e)', '343', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::729');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (227, '148', 'TCD', 'TD', 'Tchad', 'TCHAD', 'Tchadien(ne)', '344', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::148');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (232, '768', 'TGO', 'TG', 'Togo', 'TOGO', 'Togolais(e)', '345', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::768');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (248, '894', 'ZMB', 'ZM', 'Zambie', 'ZAMBIE', 'Zambien(ne)', '346', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::894');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (32, '072', 'BWA', 'BW', 'Botswana', 'BOTSWANA', 'Botswanais(e)', '347', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::072');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (125, '426', 'LSO', 'LS', 'Lesotho', 'LESOTHO', 'Lesotho', '348', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::426');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (142, '504', 'MAR', 'MA', 'Maroc', 'MAROC', 'Marocain(e)', '350', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::504');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (236, '788', 'TUN', 'TN', 'Tunisie', 'TUNISIE', 'Tunisien(ne)', '351', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::788');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (5, '012', 'DZA', 'DZ', 'Algérie', 'ALGÉRIE', 'Algerien(ne)', '352', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::012');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (145, '480', 'MUS', 'MU', 'Maurice', 'MAURICE', 'Mauricien(ne)', '390', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::480');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (222, '748', 'SWZ', 'SZ', 'Eswatini', 'ESWATINI', 'Swazilandais(e)', '391', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::748');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (94, '624', 'GNB', 'GW', 'Guinée-Bissau', 'GUINÉE-BISSAU', 'Guineen(ne) Bissau', '392', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::624');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (155, '508', 'MOZ', 'MZ', 'Mozambique', 'MOZAMBIQUE', 'Mozambiquois(e)', '393', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::508');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (206, '678', 'STP', 'ST', 'Sao Tomé-et-Principe', 'SAO TOMÉ-ET-PRINCIPE', 'Sao Tome Et Principe', '394', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::678');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (8, '024', 'AGO', 'AO', 'Angola', 'ANGOLA', 'Angolais(e)', '395', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::024');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (43, '132', 'CPV', 'CV', 'Cap-Vert', 'CABO VERDE', 'Cap Verdien(ne)', '396', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::132');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (51, '174', 'COM', 'KM', 'Comores', 'COMORES', 'Comorien(ne)', '397', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::174');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (209, '690', 'SYC', 'SC', 'Seychelles', 'SEYCHELLES', 'Seychelles', '398', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::690');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (63, '262', 'DJI', 'DJ', 'Djibouti', 'DJIBOUTI', 'Djiboutien(ne)', '399', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::262');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (42, '124', 'CAN', 'CA', 'Canada', 'CANADA', 'Canadien(ne)', '401', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::124');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (73, '840', 'USA', 'US', 'États-Unis', 'ÉTATS-UNIS', 'Americain(e)', '404', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::840');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (148, '484', 'MEX', 'MX', 'Mexique', 'MEXIQUE', 'Mexicain(e)', '405', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::484');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (57, '188', 'CRI', 'CR', 'Costa Rica', 'COSTA RICA', 'Costa Ricain(e)', '406', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::188');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (60, '192', 'CUB', 'CU', 'Cuba', 'CUBA', 'Cubain(e)', '407', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::192');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (64, '214', 'DOM', 'DO', 'République dominicaine', 'DOMINICAINE, RÉPUBLIQUE', 'Dominicain(e)', '408', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::214');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (91, '320', 'GTM', 'GT', 'Guatemala', 'GUATEMALA', 'Guatemalteque', '409', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::320');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (98, '332', 'HTI', 'HT', 'Haïti', 'HAÏTI', 'Haitien(ne)', '410', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::332');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (100, '340', 'HND', 'HN', 'Honduras', 'HONDURAS', 'Hondurien(ne)', '411', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::340');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (160, '558', 'NIC', 'NI', 'Nicaragua', 'NICARAGUA', 'Nicaraguais(e)', '412', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::558');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (175, '591', 'PAN', 'PA', 'Panama', 'PANAMA', 'Panameen(ne)', '413', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::591');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (67, '222', 'SLV', 'SV', 'Salvador', 'EL SALVADOR', 'El Salvadorien(ne)', '414', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::222');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (13, '032', 'ARG', 'AR', 'Argentine', 'ARGENTINE', 'Argentin(e)', '415', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::032');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (34, '076', 'BRA', 'BR', 'Brésil', 'BRÉSIL', 'Bresilien(ne)', '416', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::076');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (45, '152', 'CHL', 'CL', 'Chili', 'CHILI', 'Chilien(ne)', '417', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::152');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (29, '068', 'BOL', 'BO', 'Bolivie', 'BOLIVIE, ÉTAT PLURINATIONAL DE', 'Bolivien(ne)', '418', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::068');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (50, '170', 'COL', 'CO', 'Colombie', 'COLOMBIE', 'Colombien(ne)', '419', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::170');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (69, '218', 'ECU', 'EC', 'Équateur', 'ÉQUATEUR', 'Equatorien(ne)', '420', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::218');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (177, '600', 'PRY', 'PY', 'Paraguay', 'PARAGUAY', 'Paraguayen(ne)', '421', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::600');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (179, '604', 'PER', 'PE', 'Pérou', 'PÉROU', 'Peruvien(ne)', '422', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::604');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (242, '858', 'URY', 'UY', 'Uruguay', 'URUGUAY', 'Uruguayen(ne)', '423', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::858');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (244, '862', 'VEN', 'VE', 'Venezuela', 'VENEZUELA, RÉPUBLIQUE BOLIVARIENNE DU', 'Venezuelien(ne)', '424', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::862');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (115, '388', 'JAM', 'JM', 'Jamaïque', 'JAMAÏQUE', 'Jamaicain(e)', '426', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::388');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (96, '328', 'GUY', 'GY', 'Guyana', 'GUYANA', 'Guyanais(e)', '428', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::328');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (25, '084', 'BLZ', 'BZ', 'Belize', 'BELIZE', 'Belize', '429', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::084');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (235, '780', 'TTO', 'TT', 'Trinité-et-Tobago', 'TRINITÉ-ET-TOBAGO', 'Trinite Et Tobago', '433', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::780');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (22, '052', 'BRB', 'BB', 'Barbade', 'BARBADE', 'Barbade', '434', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::052');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (87, '308', 'GRD', 'GD', 'Grenade', 'GRENADE', 'Grenade Etgrenadines', '435', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::308');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (19, '044', 'BHS', 'BS', 'Bahamas', 'BAHAMAS', 'Bahamas', '436', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::044');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (220, '740', 'SUR', 'SR', 'Suriname', 'SURINAME', 'Surinamais(e)', '437', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::740');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (11, '028', 'ATG', 'AG', 'Antigua-et-Barbuda', 'ANTIGUA-ET-BARBUDA', 'Antigua Et Barbuda', '441', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::028');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (194, '659', 'KNA', 'KN', 'Saint-Christophe-et-Niévès', 'SAINT-KITTS-ET-NEVIS', 'St Christophe Nieves', '442', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::659');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (16, '036', 'AUS', 'AU', 'Australie', 'AUSTRALIE', 'Australien(ne)', '501', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::036');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (167, '554', 'NZL', 'NZ', 'Nouvelle-Zélande', 'NOUVELLE-ZÉLANDE', 'Neo Zelandais(e)', '502', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::554');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (204, '882', 'WSM', 'WS', 'Samoa', 'SAMOA', 'Samoan(ne)', '506', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::882');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (158, '520', 'NRU', 'NR', 'Nauru', 'NAURU', 'Nauru', '507', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::520');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (243, '548', 'VUT', 'VU', 'Vanuatu', 'VANUATU', 'Vanuatu', '514', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::548');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (143, '584', 'MHL', 'MH', 'Îles Marshall', 'MARSHALL, ÎLES', 'Ile Marshall', '515', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::584');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (149, '583', 'FSM', 'FM', 'États fédérés de Micronésie', 'MICRONÉSIE, ÉTATS FÉDÉRÉS DE', 'Micronesien(ne)', '516', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::583');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (77, '242', 'FJI', 'FJ', 'Fidji', 'FIDJI', 'Fidji', '508', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::242');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (122, '296', 'KIR', 'KI', 'Kiribati', 'KIRIBATI', 'Kiribati', '513', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::296');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (176, '598', 'PNG', 'PG', 'Papouasie-Nouvelle-Guinée', 'PAPOUASIE-NOUVELLE-GUINÉE', 'Papouasie', '510', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::598');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (203, '090', 'SLB', 'SB', 'Îles Salomon', 'SALOMON, ÎLES', 'Salomon', '512', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::090');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (181, '612', 'PCN', 'PN', 'Îles Pitcairn', 'PITCAIRN', 'Britannique', '503', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::612');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (184, '630', 'PRI', 'PR', 'Porto Rico', 'PORTO RICO', 'Americain(e)', '432', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::630');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (201, '654', 'SHN', 'SH', 'Sainte-Hélène, Ascension et Tristan da Cunha', 'SAINTE-HÉLÈNE, ASCENSION ET TRISTAN DA CUNHA', 'Britannique', '306', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::654');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (168, '086', 'IOT', 'IO', 'Territoire britannique de l''océan Indien', 'OCÉAN INDIEN, TERRITOIRE BRITANNIQUE DE L''', 'Britannique', '308', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::086');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (85, '292', 'GIB', 'GI', 'Gibraltar', 'GIBRALTAR', 'Britannique', '133', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::292');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (134, '807', 'MKD', 'MK', 'Macédoine du Nord', 'RÉPUBLIQUE DE MACÉDOINE', 'Macedoine Ex. Rep Yougo', '156', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::807');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (70, '232', 'ERI', 'ER', 'Érythrée', 'ÉRYTHRÉE', 'Erythree', '317', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::232');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (65, '212', 'DMA', 'DM', 'Dominique', 'DOMINIQUE', 'Dominicain(e)', '438', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::212');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (202, '662', 'LCA', 'LC', 'Sainte-Lucie', 'SAINTE-LUCIE', 'Sainte-Lucien(e)', '439', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::662');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (88, '304', 'GRL', 'GL', 'Groenland', 'GROENLAND', 'Danois(e)', '430', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::304');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (200, '670', 'VCT', 'VC', 'Saint-Vincent-et-les-Grenadines', 'SAINT-VINCENT-ET-LES-GRENADINES', 'Saint Vincent Et Grenadines Nord', '440', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::670');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (234, '776', 'TON', 'TO', 'Tonga', 'TONGA', 'Tongua Ou Friendly', '509', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::776');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (240, '798', 'TUV', 'TV', 'Tuvalu', 'TUVALU', 'Tuvalu', '511', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::798');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (174, '275', 'PSE', 'PS', 'Palestine', 'ÉTAT DE PALESTINE', 'Palestinien(ne)', '261', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::275');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (192, '732', 'ESH', 'EH', 'République arabe sahraouie démocratique', 'SAHARA OCCIDENTAL', 'Sahara Occidental', '389', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::732');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (173, '585', 'PLW', 'PW', 'Palaos', 'PALAOS', 'Palaosien(ne)', '517', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::585');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (153, '499', 'MNE', 'ME', 'Monténégro', 'MONTÉNÉGRO', 'Montenegro', '120', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::499');
INSERT INTO public.pays (id, code_iso, code_iso_alpha3, code_iso_alpha2, libelle, libelle_iso, libelle_nationalite, code_pays_apogee, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, source_id, source_code) VALUES (231, '626', 'TLS', 'TL', 'Timor oriental', 'TIMOR-LESTE', 'Timor Oriental', '262', '2022-06-27 12:52:16.043676', 1, NULL, NULL, NULL, NULL, 1, 'SYGAL::626');


--
-- Data for Name: privilege; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (601, 101, 'proposition-modification_gestion', 'Modification d''une proposition de soutenance pour gestion', 1000);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (481, 3100, 'voir-origine-non-visible', 'Voir les origines de financement masquées', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (524, 101, 'proposition-sursis', 'Accorder un sursis pour la validation d''une Modifier la proposition de soutenance', 500);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (517, 101, 'proposition-presidence', 'Générer document pour la signature par la présidence', 34);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (518, 101, 'avis-annuler', 'Annuler un avis de soutenance', 400);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (519, 101, 'avis-notifier', 'Notifier les demandes d''avis de soutenance', 500);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (520, 101, 'index-global', 'Accès à l''index global', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (521, 101, 'index-acteur', 'Accès à l''index acteur', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (500, 101, 'modification-date-rapport', 'Modification de la date de rendu des rapports', 50);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (501, 101, 'association-membre-individu', 'Associer des individus aux membres de jury', 50);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (502, 101, 'proposition-visualisation', 'Visualier de la proposition de soutenance', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (503, 101, 'proposition-modification', 'Modifier la proposition de soutenance', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (504, 101, 'proposition-validation-acteur', 'Valider/Annuler la proposition de soutenance (acteur)', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (505, 101, 'proposition-validation-ed', 'Valider/Annuler la proposition de soutenance (ecole-doctorale)', 31);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (506, 101, 'proposition-validation-ur', 'Valider/Annuler la proposition de soutenance (unite-recherche)', 32);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (507, 101, 'proposition-validation-bdd', 'Valider/Annuler la proposition de soutenance (bureau-doctorat)', 33);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (508, 101, 'engagement-impartialite-signer', 'Signer l''engagement d''impartialité ', 130);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (509, 101, 'engagement-impartialite-annuler', 'Annuler une signature  d''engagement d''impartialité', 140);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (510, 101, 'engagement-impartialite-visualiser', 'Visualiser un engagement d''impartialité', 100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (511, 101, 'engagement-impartialite-notifier', 'Notifier des demandes de signature d''engagement d''impartialité', 110);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (512, 101, 'presoutenance-visualisation', 'Visualiser les informations associées à la présoutenance', 45);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (513, 101, 'avis-visualisation', 'Visualiser l''avis de soutenance et au rapport de présoutenance', 200);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (514, 101, 'avis-modifier', 'Modifier l''avis de soutenance et le rapport de présoutenance', 300);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (522, 101, 'index-rapporteur', 'Accès à l''index rapporteur', 3);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (515, 101, 'qualite-visualisation', 'Visualiser les qualités renseignées', 400);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (516, 101, 'qualite-modification', 'Ajout/Modification des qualités ', 410);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (523, 101, 'index-structure', 'Accès à l''index structure', 4);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (421, 102, 'co-encadrant_afficher', 'Affichage des historiques de co-encadrants', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (422, 102, 'co-encadrant_gerer', 'Gérer les co-encadrants', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (661, 3160, 'envoyer', 'Envoyer un jeton utilisateur par mail', 80);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (343, 47, 'televerser', 'Téléversement de fichier commun non lié à une thèse (ex: avenant à la convention de MEL)', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (344, 47, 'telecharger', 'Téléchargement de fichier commun non lié à une thèse (ex: avenant à la convention de MEL)', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (461, 3080, 'gestion-president', 'Affichage de la gestion des Présidents du jury', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (381, 3, 'consultation-page-couverture', 'Consultation de la page de couverture', 3026);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (462, 3080, 'modifier-mail-president', 'Modification de l''email des Présidents du jury', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (463, 3080, 'notifier-president', 'Notification des Présidents du jury', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (534, 3121, 'lister-tout', 'Lister les rapports concernant toute thèse', 100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (535, 3121, 'lister-sien', 'Lister les rapports concernant ses thèses', 200);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (536, 3121, 'televerser-tout', 'Téléverser un rapport concernant toute thèse', 300);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (273, 50, 'edition', 'Édition des indicateurs', 40);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (35, 50, 'consultation-statistique', 'Consultation des statistique', 1000);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (537, 3121, 'televerser-sien', 'Téléverser un rapport concernant ses thèses', 400);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (538, 3121, 'supprimer-tout', 'Supprimer un rapport concernant toute thèse', 500);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (539, 3121, 'supprimer-sien', 'Supprimer un rapport concernant ses thèses', 600);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (540, 3121, 'telecharger-tout', 'Télécharger les rapports concernant toute thèse', 700);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (541, 3121, 'telecharger-sien', 'Télécharger les rapports concernant ses thèses', 800);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (542, 3121, 'telecharger-zip', 'Télécharger des rapports sous la forme d''une archive compressée (.zip)', 900);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (561, 3140, 'lister-tout', 'Lister les rapports concernant toute thèse', 100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (562, 3140, 'lister-sien', 'Lister les rapports concernant ses thèses', 200);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (563, 3140, 'televerser-tout', 'Téléverser un rapport concernant toute thèse', 300);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (564, 3140, 'televerser-sien', 'Téléverser un rapport concernant ses thèses', 400);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (565, 3140, 'supprimer-tout', 'Supprimer un rapport concernant toute thèse', 500);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (566, 3140, 'supprimer-sien', 'Supprimer un rapport concernant ses thèses', 600);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (567, 3140, 'telecharger-tout', 'Télécharger les rapports concernant toute thèse', 700);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (568, 3140, 'telecharger-sien', 'Télécharger les rapports concernant ses thèses', 800);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (569, 3140, 'telecharger-zip', 'Télécharger des rapports sous la forme d''une archive compressée (.zip)', 900);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (570, 3140, 'rechercher-tout', 'Rechercher des rapports concernant toute thèse', 1000);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (571, 3140, 'rechercher-sien', 'Rechercher des rapports concernant ses thèses', 1100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (572, 3141, 'lister-tout', 'Lister les rapports concernant toute thèse', 100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (573, 3141, 'lister-sien', 'Lister les rapports concernant ses thèses', 200);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (574, 3141, 'televerser-tout', 'Téléverser un rapport concernant toute thèse', 300);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (575, 3141, 'televerser-sien', 'Téléverser un rapport concernant ses thèses', 400);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (576, 3141, 'supprimer-tout', 'Supprimer un rapport concernant toute thèse', 500);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (577, 3141, 'supprimer-sien', 'Supprimer un rapport concernant ses thèses', 600);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (425, 4, 'afficher-email-contact', 'Visualiser l''email de contact du doctorant', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (578, 3141, 'telecharger-tout', 'Télécharger les rapports concernant toute thèse', 700);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (579, 3141, 'telecharger-sien', 'Télécharger les rapports concernant ses thèses', 800);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (580, 3141, 'telecharger-zip', 'Télécharger des rapports sous la forme d''une archive compressée (.zip)', 900);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (581, 3141, 'rechercher-tout', 'Rechercher des rapports concernant toute thèse', 1000);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (582, 3141, 'rechercher-sien', 'Rechercher des rapports concernant ses thèses', 1100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (583, 3121, 'valider-tout', 'Valider des rapports concernant toute thèse', 1500);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (584, 3121, 'valider-sien', 'Valider des rapports concernant ses thèses', 1600);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (585, 3121, 'devalider-tout', 'Dévalider des rapports concernant toute thèse', 1700);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (586, 3121, 'devalider-sien', 'Dévalider des rapports concernant ses thèses', 1800);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (621, 3160, 'lister', 'Lister les jetons utilisateur', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (622, 3160, 'consulter', 'Consulter un jeton utilisateur', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (623, 3160, 'creer', 'Créer un jeton utilisateur', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (624, 3160, 'modifier', 'Modifier un jeton utilisateur', 40);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (625, 3160, 'prolonger', 'Prolonger un jeton utilisateur', 50);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (626, 3160, 'supprimer', 'Supprimer un jeton utilisateur', 60);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (627, 3160, 'tester', 'Tester un jeton utilisateur', 70);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (15, 5, 'create_from_individu', 'Créer un utilisateur ''local'' à partir d''un utilisateur', 100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (482, 3101, 'intervention_index', 'Afficher la liste des interventions', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (483, 3101, 'intervention_afficher', 'Afficher une intervention', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (161, 24, 'version-papier-corrigee', 'Validation de la remise de la version papier corrigée', 4300);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (81, 3, 'telechargement-fichier', 'Téléchargement de fichier déposé', 3060);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (82, 3, 'consultation-fiche', 'Consultation de la fiche d''identité de la thèse', 3025);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (83, 3, 'consultation-depot', 'Consultation du dépôt de la thèse', 3026);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (84, 3, 'consultation-description', 'Consultation de la description de la thèse', 3027);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (85, 3, 'consultation-archivage', 'Consultation de l''archivage de la thèse', 3028);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (86, 3, 'consultation-rdv-bu', 'Consultation du rendez-vous BU', 3029);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (88, 24, 'rdv-bu', 'Validation suite au rendez-vous à la BU', 3035);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (367, 3001, 'consulter', 'Consulter une liste de diffusion', 250);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (96, 23, 'modification', 'Modification de la FAQ', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1, 1, 'role-visualisation', 'Rôles - Visualisation', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (2, 1, 'role-edition', 'Rôles - Édition', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (3, 1, 'privilege-visualisation', 'Privilèges - Visualisation', 3);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (4, 1, 'privilege-edition', 'Privilèges - Édition', 4);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (6, 2, 'ecarts', 'Écarts entre les données de l''application et ses sources', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (9, 2, 'vues-procedures', 'Mise à jour des vues différentielles et des procédures de mise à jour', 4);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (8, 2, 'tbl', 'Tableau de bord principal', 3);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (7, 2, 'maj', 'Mise à jour des données à partir de leurs sources', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (14, 5, 'attribution-role', 'Attribution de rôle aux utilisateurs', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (41, 3, 'saisie-description-version-initiale', 'Saisie de la description de la thèse', 3040);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (49, 3, 'saisie-description-version-corrigee', 'Saisie de la description de version corrigée', 3041);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (42, 3, 'saisie-autorisation-diffusion-version-initiale', 'Saisie du formulaire d''autorisation de diffusion de la version initiale', 3090);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (48, 3, 'saisie-autorisation-diffusion-version-corrigee', 'Saisie du formulaire d''autorisation de diffusion de la version corrigée', 3091);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (43, 3, 'depot-version-initiale', 'Dépôt de la version initiale de la thèse', 3050);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (44, 3, 'edition-convention-mel', 'Edition de la convention de mise en ligne', 3070);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (45, 3, 'saisie-mot-cle-rameau', 'Saisie des mots-clés RAMEAU', 3030);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (46, 5, 'consultation', 'Consultation des utilisateurs', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (47, 3, 'recherche', 'Recherche de thèses', 3010);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (61, 3, 'saisie-conformite-version-archivage-initiale', 'Juger de la conformité de la version retraitée', 3080);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (62, 3, 'saisie-conformite-version-archivage-corrigee', 'Juger de la conformité de la version corrigée retraitée', 3081);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (90, 24, 'rdv-bu-suppression', 'Suppression de la validation concernant le rendez-vous à la BU', 3036);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (368, 3001, 'lister', 'Lister les listes de diffusion', 300);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (484, 3101, 'intervention_modifier', 'Déclarer/Modifier une intervention', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (95, 5, 'modification', 'Modification d''utilisateur', 110);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (137, 3, 'depot-version-corrigee', 'Dépôt de la version corrigée de la thèse', 3055);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (127, 24, 'depot-these-corrigee', 'Validation du dépôt de la thèse corrigée', 4000);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (128, 24, 'depot-these-corrigee-suppression', 'Suppression de la validation du dépôt de la thèse corrigée', 4120);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (157, 3, 'fichier-divers-televerser', 'Téléverser un fichier comme le PV ou le rapport de soutenance, la demande de confidentialité, etc.', 100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (160, 3, 'fichier-divers-consulter', 'Télécharger/consulter un fichier comme le PV ou le rapport de soutenance, la demande de confidentialité, etc.', 150);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (177, 3, 'export-csv', 'Export des thèses au format CSV', 3020);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (197, 3, 'saisie-rdv-bu', 'Modification des informations rendez-vous BU', 3029);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (129, 24, 'correction-these', 'Validation des corrections de la thèse', 4100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (130, 24, 'correction-these-suppression', 'Suppression de la validation des corrections de la thèse', 4120);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (198, 3, 'saisie-attestations-version-initiale', 'Modification des attestations', 3030);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (199, 3, 'saisie-attestations-version-corrigee', 'Modification des attestations concernant la version corrigée', 3031);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (485, 3102, 'justificatif_index ', 'Index des justificatifs', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (486, 3102, 'justificatif_ajouter', 'Ajouter un justificatif la liste des interventions', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (22, 24, 'page-de-couverture', 'Validation de la page de couverture', 3030);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (260, 24, 'page-de-couverture-suppr', 'Suppression de la validation de la page de couverture', 3031);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (270, 50, 'consultation', 'Consultation des indicateurs', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (271, 50, 'exportation', 'Exportation des indicateurs', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (272, 50, 'rafraichissement', 'Rafraîchissement des indicateurs', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (402, 100, 'creation-ur', 'Création d''unité de recherche', 102);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (404, 100, 'consultation-de-toutes-les-structures', 'Consultation de toutes les structures', 1000);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (403, 100, 'creation-etab', 'Création d''établissement', 100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (405, 100, 'consultation-de-ses-structures', 'Consultation de ses structures', 1100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (406, 100, 'modification-de-toutes-les-structures', 'Modification de toutes les structures', 1200);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (407, 100, 'modification-de-ses-structures', 'Modification de ses structures', 1300);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (408, 3, 'consultation-de-toutes-les-theses', 'Consultation de toutes les thèses', 1000);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (409, 3, 'consultation-de-ses-theses', 'Consultation de ses thèses', 1100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (401, 100, 'creation-ed', 'Création d''école doctorale', 101);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (410, 3, 'modification-de-toutes-les-theses', 'Modification de toutes les thèses', 1200);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (411, 3, 'modification-de-ses-theses', 'Modification de ses thèses', 1300);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (281, 3, 'refresh-these', 'Réimporter la thèse', 3000);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (487, 3102, 'justificatif_retirer', 'Retirer un justificatif la liste des interventions', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (321, 3, 'saisie-correc-autorisee-forcee', 'Modification du témoin de correction autorisée forcée', 3045);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (10, 7, 'modifier-information', 'Modifier les pages d''information ', 50);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (662, 101, 'simuler_remontees', 'Simulation des remontées du jury du SI', 1000);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (682, 3, 'accorder-sursis-correction', 'Accorder un sursis pour le téléversement de la version corrigée', 3047);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (701, 3121, 'ajouter-avis-tout', 'Ajouter un avis sur un rapport concernant toute thèse', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (702, 3121, 'supprimer-avis-tout', 'Supprimer un avis sur un rapport concernant toute thèse', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (703, 3121, 'ajouter-avis-sien', 'Ajouter un avis sur un rapport concernant ses thèses', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (704, 3121, 'supprimer-avis-sien', 'Supprimer un avis sur un rapport concernant ses thèses', 40);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (721, 101, 'declaration-honneur-valider', 'Valider/Refuser la déclaration sur l''honneur de non plagiat', 1001);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (722, 101, 'declaration-honneur-revoquer', 'Revoquer la déclaration sur l''honneur de non plagiat', 1002);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (723, 3200, 'synchro-lister', 'Lister les synchros', 40);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (724, 3200, 'log-lister', 'Lister les logs', 70);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (725, 3200, 'synchro-lancer', 'Lancer une synchro', 60);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (726, 3200, 'import-lancer', 'Lancer un import', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (727, 3200, 'observation-consulter-resultat', 'Consulter les resultats d''une observation', 100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (728, 3200, 'import-lister', 'Lister les imports', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (729, 3200, 'synchro-consulter', 'Consulter une synchro', 50);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (730, 3200, 'import-consulter', 'Consulter un import', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (731, 3200, 'log-consulter', 'Consulter un log', 80);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (732, 3200, 'observation-lister', 'Lister les observations', 90);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (741, 3220, 'individucompl_afficher', 'Afficher un complément d''individu', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (742, 3220, 'individucompl_supprimer', 'Suppression un complément d''individu', 40);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (743, 3220, 'individucompl_index', 'Accéder à l''index des compléments d''individu', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (744, 3220, 'individucompl_modifier', 'Modifier un complément d''individu', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (761, 3121, 'modifier-avis-tout', 'Modifier un avis sur un rapport concernant toute thèse', 25);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (762, 3121, 'modifier-avis-sien', 'Modifier un avis sur un rapport concernant ses thèses', 26);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (781, 3220, 'modifier', 'Modifier un individu', 4);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (782, 3220, 'consulter', 'Consulter la fiche détaillée d''un individu', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (783, 3220, 'lister', 'Lister les individus', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (784, 3220, 'supprimer', 'Supprimer un individu', 5);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (785, 3220, 'ajouter', 'Créer un individu', 3);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (801, 5000, 'index_doctorant', 'Accès à l''index doctorant', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (802, 5000, 'index_formateur', 'Accès à l''index des formateurs', 3);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (803, 5000, 'index', 'Accès à l''index du module de formation', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (804, 5001, 'index', 'Accès à l''index des modules de formation', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (805, 5001, 'catalogue', 'Accéder au catalogue des formations ', 7);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (806, 5001, 'afficher', 'Afficher un module de formation', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (807, 5001, 'modifier', 'Modifier un module de formation', 4);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (808, 5001, 'historiser', 'Historiser/Restaurer un module de formation', 5);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (809, 5001, 'supprimer', 'Supprimer un module de formation', 6);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (810, 5001, 'ajouter', 'Ajouter un module de formation', 3);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (818, 5003, 'afficher', 'Afficher une session de formation', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (819, 5003, 'ajouter', 'Ajouter une session de formation', 3);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (820, 5003, 'index', 'Accès à l''index des sessions de formation', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (821, 5003, 'modifier', 'Modifier une session de formation', 4);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (822, 5003, 'gerer_inscription', 'Gerer les inscriptions d''une session de formation', 7);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (823, 5003, 'historiser', 'Historiser/Restaurer une session de formation', 5);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (824, 5003, 'supprimer', 'Supprimer une session de formation', 6);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (825, 5004, 'ajouter', 'Ajouter une séance de formation', 3);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (826, 5004, 'supprimer', 'Supprimer une séance de formation', 6);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (827, 5004, 'afficher', 'Afficher une séance de formation', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (828, 5004, 'modifier', 'Modifier une séance de formation', 4);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (829, 5004, 'historiser', 'Historiser/Restaurer une séance de formation', 5);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (830, 5004, 'index', 'Accès à l''index des séances de formation', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (831, 5004, 'renseigner_presence', 'Renseigner les présences', 7);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (832, 5005, 'supprimer', 'Supprimer une inscription de formation', 6);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (833, 5005, 'afficher', 'Afficher une inscription de formation', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (834, 5005, 'historiser', 'Historiser/Restaurer une inscription de formation', 5);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (835, 5005, 'ajouter', 'Ajouter une inscription de formation', 3);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (836, 5005, 'gerer_liste', 'Gerer la liste d''une inscription', 7);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (837, 5005, 'index', 'Accès à l''index des inscriptions de formation', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (838, 5005, 'generer_attestation', 'Gerer l''attestation', 9);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (839, 5005, 'generer_convocation', 'Generer la convoctation', 8);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (840, 5006, 'reponse_repondre', 'Répondre à l''enquête', 8);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (841, 5006, 'question_modifier', 'Modifier une question de l''enquête', 3);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (842, 5006, 'reponse_resultat', 'Afficher les résultats de l''enquête', 9);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (843, 5006, 'question_supprimer', 'Supprimer une question de l''enquête', 6);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (844, 5006, 'question_afficher', 'Afficher les questions de l''enquête', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (845, 5006, 'question_ajouter', 'Ajouter une question de l''enquête', 3);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (846, 5006, 'question_historiser', 'Historiser/Restaurer une question de l''enquête', 5);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (861, 5020, 'documentmacro_modifier', 'Modifier une macro', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (862, 5020, 'documentmacro_supprimer', 'Supprimer une macro', 40);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (863, 5020, 'documentmacro_index', 'Afficher l''index des macros', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (864, 5020, 'documentmacro_ajouter', 'Ajouter une macro', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (865, 5021, 'documenttemplate_afficher', 'Afficher un template', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (866, 5021, 'documenttemplate_index', 'Afficher l''index des contenus', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (867, 5021, 'documenttemplate_supprimer', 'Supprimer un contenu', 40);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (868, 5021, 'documenttemplate_ajouter', 'Ajouter un contenu', 15);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (869, 5021, 'documenttemplate_modifier', 'Modifier un contenu', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (870, 5022, 'documentcontenu_index', 'Accès à l''index des contenus', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (871, 5022, 'documentcontenu_afficher', 'Afficher un contenu', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (872, 5022, 'documentcontenu_supprimer', 'Supprimer un contenu ', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (881, 5002, 'afficher', 'Afficher une action de formation', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (882, 5002, 'modifier', 'Modifier une action de formation', 4);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (883, 5002, 'historiser', 'Historiser/Restaurer une action de formation', 5);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (884, 5002, 'supprimer', 'Supprimer une action de formation', 6);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (885, 5002, 'index', 'Accès à l''index des actions de formation', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (886, 5002, 'ajouter', 'Ajouter une action de formation', 3);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (901, 5040, 'log-lister', 'Lister les logs', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (902, 5040, 'log-consulter', 'Consulter les détails d''un log', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (903, 5040, 'tef-telecharger', 'Télécharger le fichier TEF envoyé', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (11, 4, 'modifier-email-contact', 'Modifier l''email de contact du doctorant', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1000, 5080, 'parametrecategorie_index', 'Affichage de l''index des paramètres', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1001, 5080, 'parametrecategorie_afficher', 'Affichage des détails d''une catégorie', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1002, 5080, 'parametrecategorie_modifier', 'Modifier une catégorie de paramètre', 40);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1003, 5080, 'parametrecategorie_supprimer', 'Supprimer une catégorie de paramètre', 60);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1004, 5080, 'parametrecategorie_ajouter', 'Ajouter une catégorie de paramètre', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1020, 3121, 'ajouter-sien', 'Ajouter un rapport d''activité concernant ses thèses', 220);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1021, 3121, 'consulter-tout', 'Consulter un rapport d''activité concernant toute thèse', 230);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1022, 3121, 'consulter-sien', 'Consulter un rapport d''activité concernant ses thèses', 240);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1023, 3121, 'generer-tout', 'Générer un rapport d''activité au format PDF concernant toute thèse', 245);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1024, 3121, 'ajouter-tout', 'Ajouter un rapport d''activité concernant toute thèse', 210);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1025, 3121, 'modifier-tout', 'Modifier un rapport d''activité concernant toute thèse', 225);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1026, 3121, 'generer-sien', 'Générer un rapport d''activité au format PDF concernant ses thèses', 246);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1027, 3121, 'modifier-sien', 'Modifier un rapport d''activité concernant ses thèses', 226);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1040, 5100, 'missionenseignement_modifier', 'Ajouter/Retirer des missions d''enseignement', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1041, 5100, 'missionenseignement_visualiser', 'Visualiser les missions d''enseignement', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1060, 101, 'proposition-revoquer-structure', 'Révoquer la validation (structure) de la proposition', 34);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1080, 4, 'lister-tout', 'Lister tous·tes les doctorant·es', 30);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1081, 4, 'lister-sien', 'Lister ses doctorant·es', 31);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1082, 4, 'consulter-tout', 'Consulter la fiche détaillée de n''importe quel·le doctorant·e', 40);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1083, 4, 'consulter-sien', 'Consulter la fiche détaillée d''un·e de ses doctorant·es', 41);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1100, 3, 'consultation-version-papier-corrigee', 'Consultation de la page consacrée à la version papier corrigée', 3100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1144, 19, 'consulter', 'Consulter les substitutions, doublons, etc.', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1145, 19, 'modifier', 'Modifier les substitutions', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1160, 5120, 'admission-modifier-avis-sien', 'Modifier un avis de son dossier d''admission', 24);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1161, 5120, 'admission-ajouter-avis-sien', 'Ajouter un avis à son dossier d''admission', 22);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1162, 5120, 'admission-afficher-son-dossier-admission', 'Consulter son dossier d''admission', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1163, 5120, 'admission-ajouter-avis-tout', 'Ajouter un avis à un dossier d''admission', 21);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1164, 5120, 'admission-supprimer-avis-tout', 'Supprimer un avis d''un dossier d''admission', 25);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1165, 5120, 'admission-convention-formation-modifier', 'Modifier la convention de formation doctorale du dossier d''admission', 22);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1166, 5120, 'admission-televerser-son-document', 'Téléverser un document dans son d''admission', 13);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1167, 5120, 'admission-modifier-avis-tout', 'Modifier un avis d''un dossier d''admission', 23);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1168, 5120, 'admission-supprimer-son-dossier-admission', 'Supprimer son dossier d''admission', 6);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1169, 5120, 'admission-gerer-recapitulatif-signe-dossier', 'Gérer l''ajout/retrait du récapitulatif signé du dossier d''admission', 17);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1170, 5120, 'admission-convention-formation-visualiser', 'Visualiser la convention de formation doctorale du dossier d''admission', 23);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1171, 5120, 'admission-telecharger-son-document', 'Télécharger un document dans son dossier d''admission', 17);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1172, 5120, 'admission-verifier', 'Ajouter des commentaires au dossier d''admission', 7);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1173, 5120, 'admission-generer-recapitulatif', 'Générer le récapitulatif du dossier d''admission', 20);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1174, 5120, 'admission-televerser-tout-document', 'Téléverser un document dans un dossier d''admission', 12);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1175, 5120, 'admission-devalider-sien', 'Dévalider un dossier d''admission', 11);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1176, 5120, 'admission-acceder-recapitulatif-signe-dossier', 'Accéder au récapitulatif signé du dossier d''admission', 17);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1177, 5120, 'admission-historiser', 'Historiser un dossier d''admission', 5);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1178, 5120, 'admission-valider-sien', 'Valider son dossier d''admission', 9);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1179, 5120, 'admission-devalider-tout', 'Dévalider son dossier d''admission', 10);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1180, 5120, 'admission-lister-mes-dossiers-admission', 'Lister mes dossiers d''admission en cours', 1);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1181, 5120, 'admission-modifier-son-dossier-admission', 'Modifier son dossier d''admission', 4);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1182, 5120, 'admission-valider-tout', 'Valider un dossier d''admission', 8);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1183, 5120, 'admission-modifier-tous-dossiers-admission', 'Modifier un dossier d''admission', 4);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1184, 5120, 'admission-afficher-tous-dossiers-admission', 'Consulter un dossier d''admission', 2);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1185, 5120, 'admission-convention-formation-generer', 'Générer la convention de formation doctorale du dossier d''admission', 24);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1186, 5120, 'admission-telecharger-tout-document', 'Télécharger un document dans un dossier d''admission', 16);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1187, 5120, 'admission-acceder-commentaires', 'Accéder à la saisie/vue des commentaires du dossier d''admission', 7);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1188, 5120, 'admission-supprimer-avis-sien', 'Supprimer un avis de son dossier d''admission', 26);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1189, 5120, 'admission-commentaires-ajoutes', 'Notifier l''étudiant des commentaires ajoutés sur son dossier d''admission', 19);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1190, 5120, 'admission-supprimer-tous-dossiers-admission', 'Supprimer un dossier d''admission', 6);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1191, 5120, 'admission-initialiser-son-dossier-admission', 'Initialiser son dossier d''admission', 27);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1192, 5120, 'admission-rechercher-dossiers-admission', 'Rechercher un dossier d''admission', 25);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1193, 5120, 'admission-supprimer-son-document', 'Supprimer un document dans son dossier d''admission', 15);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1194, 5120, 'admission-notifier-dossier-incomplet', 'Notifier l''étudiant que son dossier d''admission est incomplet', 21);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1195, 5120, 'admission-afficher-son-dossier-admission-dans-liste', 'Afficher son dossier d''admission dans la liste des dossiers d''admission en cours', 26);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1196, 5120, 'admission-supprimer-tout-document', 'Supprimer un document dans un dossier d''admission', 14);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1197, 5005, 'accorder_sursis', 'Accorder un sursis concernant la saisie de l''enquête', 847);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1220, 5160, 'modifier-acteur-de-toutes-theses', 'Modifier les acteurs de n''importe quelle thèse', 2000);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1221, 5160, 'modifier-acteur-de-ses-theses', 'Modifier les acteurs des thèses qui me concernent', 2010);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1222, 3, 'modification-domaines-hal-these', 'Modifier les domaines HAL de sa thèse', 3100);
INSERT INTO public.privilege (id, categorie_id, code, libelle, ordre) VALUES (1240, 101, 'proposition-supprimer', 'Supprimer l''ensemble des informations d''une proposition', 34);


--
-- Data for Name: type_structure; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.type_structure (id, code, libelle) VALUES (1, 'etablissement', 'Établissement');
INSERT INTO public.type_structure (id, code, libelle) VALUES (2, 'ecole-doctorale', 'École doctorale');
INSERT INTO public.type_structure (id, code, libelle) VALUES (3, 'unite-recherche', 'Unité de recherche');
INSERT INTO public.type_structure (id, code, libelle) VALUES (4, 'composante-enseignement', 'Composante d''enseignement');


--
-- Data for Name: profil; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (3, 'Administrateur', 'ADMIN', 1, NULL, 1000);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (4, 'Bureau des doctorats', 'BDD', 1, NULL, 20);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (5, 'Bibliothèque universitaire', 'BU', 1, NULL, 10);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (101, 'Rapporteur Absent', 'A', 1, NULL, 1000);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (41, 'Président du jury', 'P', 1, NULL, 300);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (6, 'Administrateur technique', 'ADMIN_TECH', NULL, NULL, 0);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (7, 'Doctorant', 'DOCTORANT', NULL, NULL, 100);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (8, 'Observateur', 'OBSERV', NULL, NULL, 1000);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (9, 'Directeur de thèse', 'D', NULL, NULL, 200);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (10, 'Co-directeur', 'K', NULL, NULL, 210);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (11, 'Rapporteur', 'R', NULL, NULL, 1000);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (12, 'Membre', 'M', NULL, NULL, 1000);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (61, 'Doctorant sans dépôt', 'NODEPOT', NULL, 'Rôle de doctorant temporaire', 1000);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (21, 'Observatoire', 'OBSERVATOIRE', NULL, NULL, 1000);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (64, 'Gestionnaire Unité de recherche', 'GEST_UR', 3, NULL, 40);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (65, 'Gestionnaire École doctorale', 'GEST_ED', 2, NULL, 30);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (1, 'Responsable Unité de recherche', 'RESP_UR', 3, NULL, 40);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (2, 'Responsable École doctorale', 'RESP_ED', 2, NULL, 30);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (81, 'Formateur·trice', 'FORMATEUR', NULL, NULL, 0);
INSERT INTO public.profil (id, libelle, role_id, structure_type, description, ordre) VALUES (102, 'Gestionnaire de formation', 'GEST_FORM', 1, NULL, 0);


--
-- Data for Name: profil_privilege; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (47, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (160, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (270, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (271, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (381, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (404, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (405, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (408, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (421, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (422, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (506, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (512, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (513, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (523, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (47, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (160, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (270, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (271, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (381, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (404, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (405, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (408, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (421, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (422, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (505, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (512, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (513, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (523, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (535, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (537, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (539, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (541, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (542, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (562, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (564, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (566, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (568, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (569, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (571, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (573, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (575, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (577, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (579, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (580, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (582, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (11, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (15, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (47, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (270, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (271, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (367, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (368, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (404, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (405, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (407, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (408, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (512, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (3, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (10, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (15, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (22, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (41, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (42, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (43, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (44, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (45, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (46, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (47, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (48, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (49, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (61, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (62, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (83, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (84, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (85, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (86, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (88, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (90, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (95, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (96, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (127, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (128, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (130, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (137, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (157, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (160, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (161, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (197, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (198, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (199, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (260, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (270, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (271, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (281, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (321, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (343, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (344, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (381, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (404, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (405, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (406, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (407, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (408, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (410, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (411, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (421, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (422, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (461, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (462, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (463, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (481, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (482, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (483, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (484, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (500, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (501, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (507, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (509, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (511, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (512, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (513, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (517, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (518, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (519, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (523, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (524, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (534, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (536, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (538, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (540, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (542, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (561, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (563, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (565, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (567, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (569, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (570, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (572, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (574, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (576, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (578, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (580, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (581, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (583, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (585, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (601, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (621, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (622, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (623, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (624, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (625, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (627, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (3, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (10, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (22, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (41, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (42, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (43, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (44, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (45, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (46, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (47, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (48, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (49, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (61, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (62, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (83, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (84, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (85, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (86, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (88, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (90, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (96, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (127, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (128, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (130, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (137, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (157, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (160, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (161, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (197, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (198, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (199, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (260, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (270, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (271, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (281, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (321, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (343, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (344, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (381, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (404, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (405, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (406, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (407, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (408, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (410, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (411, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (512, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (523, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (2, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (3, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (4, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (6, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (7, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (8, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (9, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (10, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (11, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (14, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (15, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (22, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (41, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (42, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (43, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (44, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (45, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (46, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (47, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (48, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (49, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (61, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (62, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (83, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (84, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (85, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (86, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (88, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (90, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (95, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (96, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (127, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (128, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (129, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (130, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (137, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (157, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (160, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (161, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (197, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (198, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (199, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (260, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (273, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (281, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (321, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (343, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (344, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (367, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (368, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (381, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (401, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (402, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (403, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (404, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (405, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (406, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (407, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (408, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (410, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (411, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (421, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (422, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (425, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (461, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (462, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (463, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (481, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (482, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (483, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (484, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (485, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (486, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (487, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (500, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (501, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (504, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (505, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (506, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (507, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (508, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (509, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (511, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (512, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (513, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (514, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (515, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (516, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (517, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (518, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (519, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (523, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (524, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (534, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (535, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (536, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (537, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (538, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (539, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (540, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (541, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (542, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (561, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (562, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (563, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (564, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (565, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (566, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (567, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (568, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (569, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (570, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (571, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (572, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (573, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (574, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (575, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (576, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (577, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (578, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (579, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (580, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (581, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (582, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (584, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (585, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (586, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (601, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (621, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (622, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (623, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (624, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (625, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (626, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (627, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (11, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (41, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (42, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (43, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (44, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (48, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (49, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (61, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (62, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (83, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (84, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (85, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (86, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (127, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (129, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (137, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (160, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (197, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (198, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (199, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (381, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (411, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (425, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (483, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (486, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (487, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (503, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (504, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (513, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (521, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (535, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (537, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (539, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (541, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (562, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (564, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (566, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (568, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (569, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (573, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (575, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (577, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (579, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (83, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (84, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (85, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (86, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (160, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (270, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (271, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (404, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (405, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (408, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (513, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (523, 8);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (47, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (83, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (84, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (85, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (86, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (160, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (381, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (483, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (484, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (486, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (487, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (503, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (504, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (512, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (513, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (521, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (535, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (541, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (562, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (568, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (573, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (579, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (47, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (83, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (84, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (85, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (86, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (160, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (381, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (483, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (486, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (487, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (503, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (504, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (512, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (513, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (521, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 11);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (381, 11);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 11);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (508, 11);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 11);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (513, 11);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (514, 11);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (518, 11);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 11);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (522, 11);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 12);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (381, 12);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (83, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (84, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (85, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (86, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (160, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (270, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (271, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (404, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (405, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (408, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 21);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (83, 41);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (129, 41);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 41);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (11, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (83, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (84, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (85, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (86, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (127, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (129, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (425, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (503, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (504, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (513, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (521, 61);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (518, 101);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 41);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 41);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (661, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (662, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (512, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (47, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (160, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (270, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (271, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (381, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (404, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (405, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (408, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (421, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (422, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (505, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (512, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (513, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (523, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (535, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (537, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (539, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (541, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (542, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (562, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (564, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (566, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (568, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (569, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (571, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (573, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (575, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (577, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (579, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (580, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (582, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (47, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (82, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (160, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (177, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (270, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (271, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (381, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (404, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (405, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (408, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (421, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (422, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (502, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (512, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (513, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (520, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (523, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (503, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (503, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (485, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (486, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (487, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (367, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (368, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (661, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (409, 11);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (682, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (682, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 41);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 101);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 11);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (81, 12);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (701, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (702, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (703, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (704, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (704, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (703, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (46, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (95, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (14, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (96, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (721, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (722, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (722, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (728, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (730, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (726, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (723, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (729, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (725, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (724, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (731, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (732, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (727, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (521, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (522, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (721, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (703, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (704, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (510, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (743, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (743, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (742, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (742, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (742, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (742, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (742, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (744, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (744, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (744, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (744, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (744, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (741, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (741, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (741, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (741, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (741, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (761, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (762, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (762, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (762, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (781, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (781, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (782, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (782, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (783, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (783, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (784, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (784, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (785, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (785, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (803, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (810, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (809, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (808, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (807, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (806, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (805, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (804, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (824, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (823, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (822, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (821, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (820, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (819, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (818, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (831, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (830, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (829, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (828, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (827, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (826, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (825, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (839, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (838, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (837, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (836, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (835, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (834, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (833, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (832, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (846, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (845, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (844, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (843, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (842, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (841, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (840, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (886, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (885, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (884, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (883, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (882, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (881, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (801, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (805, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (806, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (881, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (839, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (838, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (832, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (835, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (834, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (840, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (805, 81);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (806, 81);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (881, 81);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (818, 81);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (831, 81);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (827, 81);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (842, 81);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (861, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (864, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (863, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (862, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (869, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (865, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (867, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (866, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (868, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (872, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (870, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (871, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (903, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (903, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (902, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (902, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (901, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (901, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (803, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (804, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (806, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (810, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (807, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (808, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (809, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (805, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (885, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (881, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (886, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (882, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (883, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (884, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (820, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (818, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (819, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (821, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (823, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (824, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (822, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (830, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (827, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (825, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (828, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (829, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (826, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (831, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (837, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (833, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (835, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (832, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (834, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (836, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (839, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (838, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (844, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (845, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (841, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (846, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (843, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (840, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (842, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (157, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (803, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (804, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (806, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (805, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (885, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (881, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (820, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (818, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (830, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (827, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1027, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1027, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1026, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1026, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1025, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1025, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1024, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1024, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1023, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1023, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1022, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1022, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1021, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1021, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1020, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1020, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1027, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1027, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1020, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1020, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1026, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1026, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1026, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1026, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1026, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1026, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1026, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (541, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (541, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (541, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1022, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1022, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1022, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1022, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1022, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1022, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1022, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (535, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1020, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (584, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (584, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (703, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (762, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (703, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (762, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (703, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (762, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (586, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (584, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (535, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (535, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (562, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (562, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (573, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (573, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1060, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1060, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1060, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1040, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1040, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1040, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (515, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (516, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1041, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1041, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1041, 3);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (367, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (368, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1080, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1080, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1080, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1082, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1082, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1082, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1081, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1081, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1081, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1081, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1081, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1081, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1083, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1083, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1083, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1083, 64);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1083, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1083, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1083, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (803, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (804, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (806, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (810, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (807, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (808, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (805, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (885, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (886, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (882, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (883, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (881, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (820, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (818, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (819, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (821, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (823, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (822, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (830, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (827, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (825, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (828, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (829, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (831, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (837, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (833, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (835, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (834, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (836, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (839, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (838, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (844, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (841, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (845, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (846, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (842, 102);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (270, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (271, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1100, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1100, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1100, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1100, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1100, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1144, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1145, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1160, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1161, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1162, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1163, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1164, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1165, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1166, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1167, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1168, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1169, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1170, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1171, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1173, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1174, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1175, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1176, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1177, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1178, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1179, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1180, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1181, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1182, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1183, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1184, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1185, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1186, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1187, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1188, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1189, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1190, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1192, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1193, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1194, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1195, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1196, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1195, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1195, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1195, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1195, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1195, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1192, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1192, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1192, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1192, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1192, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1188, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1188, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1188, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1188, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1188, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1187, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1187, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1187, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1187, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1187, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1185, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1185, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1185, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1185, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1185, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1180, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1180, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1180, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1180, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1180, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1178, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1178, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1178, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1178, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1178, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1176, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1176, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1176, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1176, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1176, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1175, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1175, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1175, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1175, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1175, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1171, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1171, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1171, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1171, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1171, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1170, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1170, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1170, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1170, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1170, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1162, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1162, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1162, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1162, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1162, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1161, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1161, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1161, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1161, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1161, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1160, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1160, 10);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1160, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1160, 1);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1160, 2);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1176, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1171, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1185, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1195, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1162, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1180, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1187, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1172, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1173, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1189, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1169, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1194, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1168, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1181, 9);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1197, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1197, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1221, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1220, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1220, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1222, 4);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1222, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1222, 7);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1222, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1240, 6);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (1240, 65);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (781, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (782, 5);
INSERT INTO public.profil_privilege (privilege_id, profil_id) VALUES (583, 6);


--
-- Data for Name: soutenance_etat; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.soutenance_etat (id, code, libelle, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, ordre) VALUES (1, 'EN_COURS', 'En cours d''examen', '2020-09-21 10:50:03', 1, '2020-09-21 10:50:03', 1, NULL, NULL, 1);
INSERT INTO public.soutenance_etat (id, code, libelle, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, ordre) VALUES (4, 'ETABLISSEMENT', 'Validation du dossier par l établissement', '2020-09-21 10:50:04', 1, '2020-09-21 10:50:04', 1, NULL, NULL, 2);
INSERT INTO public.soutenance_etat (id, code, libelle, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, ordre) VALUES (5, 'COMPLET', 'Avis de soutenance en cours de validation au chef d’établissement', '2023-04-05 07:06:44.507203', 1, '2023-04-05 07:06:44.507203', 1, NULL, NULL, 3);
INSERT INTO public.soutenance_etat (id, code, libelle, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, ordre) VALUES (2, 'VALIDEE', 'Soutenance autorisée', '2020-09-21 10:50:04', 1, '2020-09-21 10:50:04', 1, NULL, NULL, 10);
INSERT INTO public.soutenance_etat (id, code, libelle, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, ordre) VALUES (3, 'REJETEE', 'Soutenance rejetée', '2020-09-21 10:50:04', 1, '2020-09-21 10:50:04', 1, NULL, NULL, 20);


--
-- Data for Name: soutenance_qualite; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (594, 'Enseignant- Chercheur', 'B', 'N', 'N', '2023-10-24 10:26:27', 1461, '2023-10-24 10:26:27', 1461, NULL, NULL, 'N', NULL);
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (4, 'Maître de conférences', 'B', 'N', 'N', '2020-09-21 11:05:34', 1, '2020-09-21 11:05:34', 1, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (0, 'Maître de Conférences HDR - Praticien Hospitalier', 'B', 'O', 'N', '2021-10-27 16:38:18', 1446, '2021-10-27 16:38:18', 1446, NULL, NULL, 'N', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (554, 'Maître de Conférences HDR- Emerite', 'B', 'O', 'O', '2023-09-01 15:08:43', 1461, '2023-09-01 15:08:43', 1461, NULL, NULL, 'O', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (12, 'Chercheur', 'B', 'N', 'N', '2020-09-21 11:05:34', 1, '2021-10-27 11:24:15', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (16, 'Adjoint administratif', 'B', 'N', 'N', '2021-04-28 09:17:37', 1446, '2021-10-27 11:37:48', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (174, 'Assistant ingénieur', 'B', 'N', 'N', '2021-10-27 11:38:52', 1461, '2021-10-27 11:38:52', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (11, 'Ingénieur d''Etudes ', 'B', 'N', 'N', '2020-09-21 11:05:34', 1, '2021-10-27 11:32:56', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (574, 'Maître de Conéfrences Emerite', 'B', 'N', 'O', '2023-09-01 15:09:20', 1461, '2023-09-01 15:09:20', 1461, NULL, NULL, 'O', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (434, 'Docent', 'B', 'N', 'N', '2021-10-27 11:50:36', 1461, '2021-10-27 11:50:36', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (94, 'Maître de Conférences - Praticien Hospitalier', 'B', 'N', 'N', '2021-10-07 15:25:14', 1446, '2021-10-27 11:33:53', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (7, 'Professeur des universités émerite', 'A', 'O', 'O', '2020-09-21 11:05:34', 1, '2021-10-27 16:37:08', 1446, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (394, ' Praticien Hospitalier', 'B', 'N', 'N', '2021-10-27 11:44:33', 1461, '2021-10-27 11:44:33', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (254, 'Cadre de recherche', 'B', 'N', 'N', '2021-10-27 11:41:03', 1461, '2021-10-27 11:41:03', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (5, 'Chargé de recherche', 'B', 'N', 'N', '2020-09-21 11:05:34', 1, '2020-09-21 11:05:34', 1, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (114, 'Directeur de recherche émérite', 'A', 'O', 'O', '2021-10-27 11:17:13', 1461, '2021-10-27 16:36:56', 1446, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (474, 'Autre membre de rang A', 'A', 'O', 'N', '2022-04-08 15:45:43', 1461, '2023-01-23 13:46:08', 1446, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (134, 'Professeur (dans un établissement à l''étranger)', 'A', 'O', 'N', '2021-10-27 11:19:13', 1461, '2021-10-27 16:39:19', 1446, NULL, NULL, 'O', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (454, 'Membre étranger de rang B HDR', 'B', 'O', 'N', '2021-11-16 16:59:01', 1446, '2022-07-19 08:17:48', 1446, NULL, NULL, 'O', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (214, 'Assistant professor', 'B', 'N', 'N', '2021-10-27 11:39:46', 1461, '2021-10-27 11:39:46', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (8, 'Chargé de Recherche HDR', 'B', 'O', 'N', '2020-09-21 11:05:34', 1, '2021-10-27 16:37:17', 1446, NULL, NULL, 'N', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (10, 'Ingénieur de Recherche HDR', 'B', 'O', 'N', '2020-09-21 11:05:34', 1, '2021-10-27 16:37:54', 1446, NULL, NULL, 'N', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (6, 'Maître de conférences HDR', 'B', 'O', 'N', '2020-09-21 11:05:34', 1, '2021-10-27 16:38:07', 1446, NULL, NULL, 'N', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (154, 'Ingenieur d''études HDR', 'B', 'O', 'N', '2021-10-27 11:32:23', 1461, '2021-10-27 16:37:41', 1446, NULL, NULL, 'N', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (374, 'Médecin', 'B', 'N', 'N', '2021-10-27 11:44:01', 1461, '2021-10-27 11:44:01', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (494, 'Enseignant Chercheur retraité', ' ', 'N', 'N', '2022-04-29 15:26:58', 1461, '2022-04-29 15:26:58', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (2, 'Directeur de recherche', 'A', 'O', 'N', '2020-09-21 11:05:34', 1, '2020-09-21 11:05:34', 1, NULL, NULL, 'N', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (274, 'Chef d''entreprise', 'B', 'N', 'N', '2021-10-27 11:41:25', 1461, '2021-10-27 11:41:25', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (414, 'Senior lecturer', 'B', 'N', 'N', '2021-10-27 11:45:13', 1461, '2021-10-27 11:45:36', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (537, 'Associate Professor - Équivalent HDR', 'B', 'O', 'N', '2023-04-25 12:52:30.492962', 1, '2023-04-25 12:52:30.492962', 1, NULL, NULL, 'N', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (54, 'Chercheur HDR', 'B', 'O', 'N', '2021-07-07 09:07:44', 1461, '2021-10-27 16:37:24', 1446, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (9, 'Ingénieur de Recherche ', 'B', 'N', 'N', '2020-09-21 11:05:34', 1, '2020-09-21 11:05:34', 1, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (354, 'Maître assistant', 'B', 'N', 'N', '2021-10-27 11:43:39', 1461, '2021-10-27 11:43:39', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (1, 'Professeur des universités (université Française)', 'A', 'O', 'N', '2020-09-21 11:05:34', 1, '2021-10-27 11:15:18', 1461, NULL, NULL, 'N', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (34, 'Membre étranger de rang A', 'A', 'O', 'N', '2021-05-28 15:22:14', 1446, '2021-10-27 11:14:29', 1461, NULL, NULL, 'O', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (536, 'Autre membre de rang B', 'B', 'N', 'N', '2023-04-25 12:46:21.594642', 1, '2023-04-25 12:46:21.594642', 1, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (294, 'Directeur', 'B', 'N', 'N', '2021-10-27 11:42:01', 1461, '2021-10-27 11:42:01', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (514, 'Ingénieur des ponts, eaux  et forêts', 'A', 'O', 'N', '2023-01-23 13:48:14', 1446, '2023-01-23 13:48:14', 1446, NULL, NULL, 'N', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (74, 'Professeur des universités-praticien hospitalier ', 'A', 'O', 'N', '2021-07-12 13:43:02', 1446, '2021-07-12 13:43:02', 1446, NULL, NULL, 'N', 'O');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (194, 'Associate Professor', 'B', 'N', 'N', '2021-10-27 11:39:18', 1461, '2021-10-27 11:39:18', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (234, 'Chargé d''enseignement', 'B', 'N', 'N', '2021-10-27 11:40:14', 1461, '2021-10-27 11:45:58', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (314, 'Docteur', 'B', 'N', 'N', '2021-10-27 11:42:26', 1461, '2021-10-27 11:42:26', 1461, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (334, 'Docteur HDR', 'B', 'O', 'N', '2021-10-27 11:42:48', 1461, '2021-10-27 16:37:33', 1446, NULL, NULL, 'N', 'N');
INSERT INTO public.soutenance_qualite (id, libelle, rang, hdr, emeritat, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, justificatif, admission) VALUES (35, 'Membre étranger de rang B', 'B', 'N', 'N', '2021-05-28 15:22:37', 1446, '2021-05-28 15:22:37', 1446, NULL, NULL, 'O', 'N');


--
-- Data for Name: soutenance_qualite_sup; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.soutenance_qualite_sup (id, qualite_id, libelle, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (1, 5, 'CHARGE DE RECHERCHE', '2019-12-17 10:01:15', 1, '2019-12-17 10:01:22', 1, NULL, NULL);
INSERT INTO public.soutenance_qualite_sup (id, qualite_id, libelle, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (4, 1, 'PROFESSEUR DES UNIVERSITES', '2019-12-17 11:54:12', 1446, '2019-12-17 11:54:12', 1446, NULL, NULL);
INSERT INTO public.soutenance_qualite_sup (id, qualite_id, libelle, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (2, 2, 'DIRECTEUR DE RECHERCHE', '2019-12-17 11:26:10', 1446, '2019-12-17 11:26:10', 1446, NULL, NULL);
INSERT INTO public.soutenance_qualite_sup (id, qualite_id, libelle, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (3, 1, 'PROFESSEUR DES UNIVERSITÉS', '2019-12-17 11:27:29', 1446, '2019-12-17 11:27:29', 1446, NULL, NULL);


--
-- Data for Name: type_rapport; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.type_rapport (id, code, libelle_court, libelle_long) VALUES (1, 'RAPPORT_ACTIVITE', 'Activité', 'Rapport d''activité');
INSERT INTO public.type_rapport (id, code, libelle_court, libelle_long) VALUES (3, 'RAPPORT_CSI', 'CSI', 'Rapport CSI');
INSERT INTO public.type_rapport (id, code, libelle_court, libelle_long) VALUES (4, 'RAPPORT_MIPARCOURS', 'Mi-parcours', 'Rapport mi-parcours');


--
-- Data for Name: type_validation; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.type_validation (id, code, libelle) VALUES (1, 'RDV_BU', 'Validation suite au rendez-vous avec le doctorant');
INSERT INTO public.type_validation (id, code, libelle) VALUES (2, 'DEPOT_THESE_CORRIGEE', 'Validation automatique du dépôt de la thèse corrigée');
INSERT INTO public.type_validation (id, code, libelle) VALUES (3, 'CORRECTION_THESE', 'Validation par le président du jury des corrections de la thèse');
INSERT INTO public.type_validation (id, code, libelle) VALUES (4, 'VERSION_PAPIER_CORRIGEE', 'Confirmation dépot de la version papier corrigée');
INSERT INTO public.type_validation (id, code, libelle) VALUES (5, 'PAGE_DE_COUVERTURE', 'Validation de la page de couverture');
INSERT INTO public.type_validation (id, code, libelle) VALUES (6, 'PROPOSITION_SOUTENANCE', 'Validation de la proposition de soutenance');
INSERT INTO public.type_validation (id, code, libelle) VALUES (8, 'ENGAGEMENT_IMPARTIALITE', 'Signature de l''engagement d''impartialité');
INSERT INTO public.type_validation (id, code, libelle) VALUES (9, 'VALIDATION_PROPOSITION_ED', 'Validation de la proposition de soutenance par l''école doctorale');
INSERT INTO public.type_validation (id, code, libelle) VALUES (10, 'VALIDATION_PROPOSITION_UR', 'Validation de la proposition de soutenance par l''unité de recherche');
INSERT INTO public.type_validation (id, code, libelle) VALUES (11, 'VALIDATION_PROPOSITION_BDD', 'Validation de la proposition de soutenance par le bureau des doctorats');
INSERT INTO public.type_validation (id, code, libelle) VALUES (12, 'AVIS_SOUTENANCE', 'Signature de l''avis de soutenance');
INSERT INTO public.type_validation (id, code, libelle) VALUES (13, 'REFUS_ENGAGEMENT_IMPARTIALITE', 'Refus de l''engagement d''impartialité');
INSERT INTO public.type_validation (id, code, libelle) VALUES (14, 'RAPPORT_CSI', 'Validation de rapport CSI');
INSERT INTO public.type_validation (id, code, libelle) VALUES (15, 'RAPPORT_MIPARCOURS', 'Validation de rapport mi-parcours');
INSERT INTO public.type_validation (id, code, libelle) VALUES (21, 'DOCTORANT_DECLARATION_HONNEUR_NON_PLAGIAT', 'Déclaration sur l''honneur de non plagiat du doctorant');
INSERT INTO public.type_validation (id, code, libelle) VALUES (22, 'DOCTORANT_REFUS_HONNEUR_NON_PLAGIAT', 'Refus de la déclaration sur l''honneur de non plagiat du doctorant');
INSERT INTO public.type_validation (id, code, libelle) VALUES (7, 'RAPPORT_ACTIVITE_AUTO', 'Validation finale du rapport d''activité non dématérialisé (ancien module)');
INSERT INTO public.type_validation (id, code, libelle) VALUES (41, 'RAPPORT_ACTIVITE_DOCTORANT', 'Validation électronique du rapport d''activité par le doctorant');
INSERT INTO public.type_validation (id, code, libelle) VALUES (42, 'RAPPORT_ACTIVITE', 'Validation finale du rapport d''activité non dématérialisé (ancien module)');


--
-- Data for Name: unicaen_avis_type; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.unicaen_avis_type (id, code, libelle, description, ordre) VALUES (1, 'AVIS_RAPPORT_ACTIVITE_GEST', 'Complétude du rapport d''activité (ancien module)', 'Point de vue Gestionnaire d''ED', 10);
INSERT INTO public.unicaen_avis_type (id, code, libelle, description, ordre) VALUES (3, 'AVIS_RAPPORT_ACTIVITE_DIR_THESE', 'Avis et validation électronique de la direction de thèse', 'Point de vue de la direction de thèse', 20);
INSERT INTO public.unicaen_avis_type (id, code, libelle, description, ordre) VALUES (4, 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE', 'Avis et validation électronique de la codirection de thèse', 'Point de vue de la codirection de thèse', 20);
INSERT INTO public.unicaen_avis_type (id, code, libelle, description, ordre) VALUES (5, 'AVIS_RAPPORT_ACTIVITE_DIR_UR', 'Avis et validation électronique de la direction de l''unité de recherche', 'Point de vue de la direction d''UR', 20);
INSERT INTO public.unicaen_avis_type (id, code, libelle, description, ordre) VALUES (2, 'AVIS_RAPPORT_ACTIVITE_DIR_ED', 'Avis et validation électronique de la direction de l''école doctorale', 'Point de vue de la Direction d''ED', 20);
INSERT INTO public.unicaen_avis_type (id, code, libelle, description, ordre) VALUES (6, 'AVIS_ADMISSION_DIR_THESE', 'Avis de la direction de thèse', 'Point de vue de la direction de thèse', 30);
INSERT INTO public.unicaen_avis_type (id, code, libelle, description, ordre) VALUES (7, 'AVIS_ADMISSION_CODIR_THESE', 'Avis de la codirection de thèse', 'Point de vue de la codirection de thèse', 30);
INSERT INTO public.unicaen_avis_type (id, code, libelle, description, ordre) VALUES (8, 'AVIS_ADMISSION_DIR_UR', 'Avis de la direction de l''unité de recherche', 'Point de vue de la direction d''UR', 30);
INSERT INTO public.unicaen_avis_type (id, code, libelle, description, ordre) VALUES (9, 'AVIS_ADMISSION_DIR_ED', 'Avis de la direction de l''école doctorale', 'Point de vue de la direction d''ED', 30);
INSERT INTO public.unicaen_avis_type (id, code, libelle, description, ordre) VALUES (10, 'AVIS_ADMISSION_PRESIDENCE', 'Signature du dossier par la direction de l''établissement', 'Point de vue de la présidence de l''établissement d''inscription', 30);


--
-- Data for Name: unicaen_avis_valeur; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.unicaen_avis_valeur (id, code, valeur, valeur_bool, tags, ordre, description) VALUES (1, 'AVIS_RAPPORT_ACTIVITE_VALEUR_COMPLET', 'Rapport complet', true, 'icon-ok', 1, NULL);
INSERT INTO public.unicaen_avis_valeur (id, code, valeur, valeur_bool, tags, ordre, description) VALUES (2, 'AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET', 'Rapport incomplet', false, 'icon-ko', 2, NULL);
INSERT INTO public.unicaen_avis_valeur (id, code, valeur, valeur_bool, tags, ordre, description) VALUES (4, 'AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF', 'Avis positif', true, 'icon-ok', 4, NULL);
INSERT INTO public.unicaen_avis_valeur (id, code, valeur, valeur_bool, tags, ordre, description) VALUES (5, 'AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF', 'Avis réservé', false, 'icon-ko', 5, NULL);
INSERT INTO public.unicaen_avis_valeur (id, code, valeur, valeur_bool, tags, ordre, description) VALUES (3, 'AVIS_RAPPORT_ACTIVITE_DIR_ED_VALEUR_INCOMPLET', 'Rapport incomplet', false, 'icon-ko', 3, NULL);
INSERT INTO public.unicaen_avis_valeur (id, code, valeur, valeur_bool, tags, ordre, description) VALUES (6, 'AVIS_ADMISSION_VALEUR_COMPLET', 'Dossier d''admission complet', true, 'icon-ok', 6, NULL);
INSERT INTO public.unicaen_avis_valeur (id, code, valeur, valeur_bool, tags, ordre, description) VALUES (7, 'AVIS_ADMISSION_VALEUR_INCOMPLET', 'Dossier d''admission incomplet', false, 'icon-ko', 7, NULL);
INSERT INTO public.unicaen_avis_valeur (id, code, valeur, valeur_bool, tags, ordre, description) VALUES (8, 'AVIS_ADMISSION_VALEUR_POSITIF', 'Avis positif', true, 'icon-ok', 8, NULL);
INSERT INTO public.unicaen_avis_valeur (id, code, valeur, valeur_bool, tags, ordre, description) VALUES (9, 'AVIS_ADMISSION_VALEUR_NEGATIF', 'Avis réservé', false, 'icon-ko', 9, NULL);


--
-- Data for Name: unicaen_avis_type_valeur; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (1, 1, 1);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (2, 1, 2);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (3, 2, 3);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (4, 2, 4);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (5, 2, 5);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (6, 3, 2);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (7, 3, 4);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (8, 3, 5);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (9, 4, 4);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (10, 4, 5);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (11, 5, 4);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (12, 5, 5);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (13, 6, 7);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (14, 6, 8);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (15, 6, 9);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (16, 7, 7);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (17, 7, 8);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (18, 7, 9);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (19, 8, 7);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (20, 8, 8);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (21, 8, 9);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (22, 9, 7);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (23, 9, 8);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (24, 9, 9);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (25, 10, 8);
INSERT INTO public.unicaen_avis_type_valeur (id, avis_type_id, avis_valeur_id) VALUES (26, 10, 9);


--
-- Data for Name: unicaen_avis_type_valeur_complem; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (1, 2, NULL, 'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__PB_DOCTORANT', 'Pas de date/signature du doctorant', 'checkbox', 10, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (2, 2, NULL, 'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__MANQUE_DATE_SIGNATURE_DIRECTION_THESE', 'Manque la date/signature de la direction de thèse', 'checkbox', 20, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (13, 2, NULL, 'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__MANQUE_AVIS_DIRECTION_THESE', 'Manque l''avis de la direction de thèse', 'checkbox', 25, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (3, 2, NULL, 'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__MANQUE_DATE_SIGNATURE_DIRECTION_UR', 'Manque la date/signature de la Direction de l''Unité de Recherche', 'checkbox', 30, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (14, 2, NULL, 'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__MANQUE_AVIS_DIRECTION_UR', 'Manque l''avis de la Direction de l''Unité de Recherche', 'checkbox', 35, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (4, 2, NULL, 'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__PB_AUTRE', 'Autre (à préciser)', 'checkbox', 40, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (5, 2, 4, 'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__PB_AUTRE_PRECISION', 'Précisions :', 'textarea', 50, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (6, 2, NULL, 'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__PB_COMMENTAIRES', 'Commentaires', 'textarea', 60, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (7, 1, NULL, 'AVIS_RAPPORT_ACTIVITE_GEST__AVIS_RAPPORT_ACTIVITE_VALEUR_COMPLET__PB_COMMENTAIRES', 'Commentaires', 'textarea', 70, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (9, 5, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR__AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF__PB_MOTIF', 'Motif', 'textarea', 90, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (10, 3, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR__AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET__PB_COMMENTAIRES', 'Commentaires', 'textarea', 100, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (11, 4, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR__AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 110, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (12, 5, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR__AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 120, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (8, 3, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR__AVIS_RAPPORT_ACTIVITE_DIR_VALEUR_INCOMPLET__PB_INFOS', 'Si le rapport est jugé incomplet, la balle retournera dans le camp du doctorant...', 'information', 80, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (15, 6, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR_THESE__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__PB_INFOS', 'Si le rapport est jugé incomplet, la balle retournera dans le camp du doctorant...', 'information', 15, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (16, 8, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR_THESE__AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF__PB_MOTIF', 'Motif', 'textarea', 16, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (17, 6, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR_THESE__AVIS_RAPPORT_ACTIVITE_VALEUR_INCOMPLET__PB_COMMENTAIRES', 'Commentaires', 'textarea', 17, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (18, 7, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR_THESE__AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 18, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (19, 8, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR_THESE__AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 19, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (20, 10, NULL, 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE__AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF__PB_MOTIF', 'Motif', 'textarea', 20, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (21, 9, NULL, 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE__AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 21, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (22, 10, NULL, 'AVIS_RAPPORT_ACTIVITE_CODIR_THESE__AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 22, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (23, 12, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR_UR__AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF__PB_MOTIF', 'Motif', 'textarea', 23, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (24, 11, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR_UR__AVIS_RAPPORT_ACTIVITE_VALEUR_POSITIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 24, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (25, 12, NULL, 'AVIS_RAPPORT_ACTIVITE_DIR_UR__AVIS_RAPPORT_ACTIVITE_VALEUR_NEGATIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 25, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (26, 13, NULL, 'AVIS_ADMISSION_DIR_THESE__AVIS_ADMISSION_VALEUR_INCOMPLET__PB_INFOS', 'Si le dossier d''admission est jugé incomplet, l''étudiant devra reprendre le circuit de signatures depuis le début...', 'information', 26, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (27, 13, NULL, 'AVIS_ADMISSION_DIR_THESE__AVIS_ADMISSION_VALEUR_INCOMPLET__PB_MOTIF', 'Motif', 'textarea', 27, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (28, 15, NULL, 'AVIS_ADMISSION_DIR_THESE__AVIS_ADMISSION_VALEUR_NEGATIF__PB_MOTIF', 'Motif', 'textarea', 28, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (29, 13, NULL, 'AVIS_ADMISSION_DIR_THESE__AVIS_ADMISSION_VALEUR_INCOMPLET__PB_COMMENTAIRES', 'Commentaires', 'textarea', 29, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (30, 14, NULL, 'AVIS_ADMISSION_DIR_THESE__AVIS_ADMISSION_VALEUR_POSITIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 30, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (31, 15, NULL, 'AVIS_ADMISSION_DIR_THESE__AVIS_ADMISSION_VALEUR_NEGATIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 31, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (32, 16, NULL, 'AVIS_ADMISSION_CODIR_THESE__AVIS_ADMISSION_VALEUR_INCOMPLET__PB_INFOS', 'Si le dossier d''admission est jugé incomplet, l''étudiant devra reprendre le circuit de signatures depuis le début...', 'information', 32, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (33, 16, NULL, 'AVIS_ADMISSION_CODIR_THESE__AVIS_ADMISSION_VALEUR_INCOMPLET__PB_MOTIF', 'Motif', 'textarea', 33, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (34, 18, NULL, 'AVIS_ADMISSION_CODIR_THESE__AVIS_ADMISSION_VALEUR_NEGATIF__PB_MOTIF', 'Motif', 'textarea', 34, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (35, 16, NULL, 'AVIS_ADMISSION_CODIR_THESE__AVIS_ADMISSION_VALEUR_INCOMPLET__PB_COMMENTAIRES', 'Commentaires', 'textarea', 35, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (36, 19, NULL, 'AVIS_ADMISSION_DIR_UR__AVIS_ADMISSION_VALEUR_INCOMPLET__PB_INFOS', 'Si le dossier d''admission est jugé incomplet, l''étudiant devra reprendre le circuit de signatures depuis le début...', 'information', 36, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (37, 19, NULL, 'AVIS_ADMISSION_DIR_UR__AVIS_ADMISSION_VALEUR_INCOMPLET__PB_MOTIF', 'Motif', 'textarea', 37, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (38, 21, NULL, 'AVIS_ADMISSION_DIR_UR__AVIS_ADMISSION_VALEUR_NEGATIF__PB_MOTIF', 'Motif', 'textarea', 38, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (39, 19, NULL, 'AVIS_ADMISSION_DIR_UR__AVIS_ADMISSION_VALEUR_INCOMPLET__PB_COMMENTAIRES', 'Commentaires', 'textarea', 39, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (40, 20, NULL, 'AVIS_ADMISSION_DIR_UR__AVIS_ADMISSION_VALEUR_POSITIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 40, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (41, 21, NULL, 'AVIS_ADMISSION_DIR_UR__AVIS_ADMISSION_VALEUR_NEGATIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 41, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (42, 22, NULL, 'AVIS_ADMISSION_DIR_ED__AVIS_ADMISSION_VALEUR_INCOMPLET__PB_INFOS', 'Si le dossier d''admission est jugé incomplet, l''étudiant devra reprendre le circuit de signatures depuis le début...', 'information', 42, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (43, 22, NULL, 'AVIS_ADMISSION_DIR_ED__AVIS_ADMISSION_VALEUR_INCOMPLET__PB_MOTIF', 'Motif', 'textarea', 43, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (44, 24, NULL, 'AVIS_ADMISSION_DIR_ED__AVIS_ADMISSION_VALEUR_NEGATIF__PB_MOTIF', 'Motif', 'textarea', 44, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (45, 22, NULL, 'AVIS_ADMISSION_DIR_ED__AVIS_ADMISSION_VALEUR_INCOMPLET__PB_COMMENTAIRES', 'Commentaires', 'textarea', 45, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (46, 23, NULL, 'AVIS_ADMISSION_DIR_ED__AVIS_ADMISSION_VALEUR_POSITIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 46, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (47, 24, NULL, 'AVIS_ADMISSION_DIR_ED__AVIS_ADMISSION_VALEUR_NEGATIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 47, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (48, 26, NULL, 'AVIS_ADMISSION_PRESIDENCE__AVIS_ADMISSION_VALEUR_NEGATIF__PB_MOTIF', 'Motif', 'textarea', 48, false, true);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (49, 25, NULL, 'AVIS_ADMISSION_PRESIDENCE__AVIS_ADMISSION_VALEUR_POSITIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 49, false, false);
INSERT INTO public.unicaen_avis_type_valeur_complem (id, avis_type_valeur_id, parent_id, code, libelle, type, ordre, obligatoire, obligatoire_un_au_moins) VALUES (50, 26, NULL, 'AVIS_ADMISSION_PRESIDENCE__AVIS_ADMISSION_VALEUR_NEGATIF__PB_COMMENTAIRES', 'Commentaires', 'textarea', 50, false, false);


--
-- Data for Name: unicaen_parametre_categorie; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.unicaen_parametre_categorie (id, code, libelle, description, ordre) VALUES (1, 'SOUTENANCE', 'Gestion des paramètres du module Soutenance', NULL, 1000);
INSERT INTO public.unicaen_parametre_categorie (id, code, libelle, description, ordre) VALUES (2, 'FORMATION', 'Gestion des paramètres du module Formation', NULL, 900);
INSERT INTO public.unicaen_parametre_categorie (id, code, libelle, description, ordre) VALUES (3, 'RAPPORT_ACTIVITE', 'Paramètres du module Rapports d''activité', NULL, 100);
INSERT INTO public.unicaen_parametre_categorie (id, code, libelle, description, ordre) VALUES (4, 'ANNEE_UNIV', 'Années universitaires', 'Paramètres concernant les années universitaires', 50);


--
-- Data for Name: unicaen_parametre_parametre; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (1, 1, 'NB_MIN_RAPPORTEUR', 'Nombre minimal de rapporteurs', NULL, 'Number', '2', 300);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (2, 1, 'RATIO_MIN_RANG_A', 'Ratio minimal de membres de rang A', NULL, 'String', '0.5', 500);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (3, 1, 'DELAI_INTERVENTION', 'Délai permettant aux directeurs d''intervenir [-j jour:+j jour]', NULL, 'Number', '21', 1200);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (4, 1, 'DOC_DELOCALISATION', 'Formulaire de délocalisation de la soutenance', NULL, 'String', 'https://sygal.normandie-univ.fr/fichier/telecharger/permanent/DEMANDE_DELOCALISATION_SOUTENANCE', 2100);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (6, 1, 'DOC_REDACTION_ANGLAIS', 'Formulaire de demande de rédaction en anglais', NULL, 'String', NULL, 2400);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (7, 1, 'DOC_LABEL_EUROPEEN', 'Formulaire de demande de label europeen', NULL, 'String', 'https://sygal.normandie-univ.fr/fichier/telecharger/permanent/DEMANDE_LABEL_EUROPEEN', 2300);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (9, 1, 'NB_MIN_MEMBRE_JURY', 'Nombre minimal de membres dans le jury', NULL, 'Number', '4', 10);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (11, 1, 'RATIO_MIN_EXTERIEUR', 'Ratio minimal de membres extérieurs', NULL, 'String', '0.5', 600);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (12, 1, 'NB_MAX_MEMBRE_JURY', 'Nombre maximal de membres dans le jury', NULL, 'Number', '8', 20);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (13, 1, 'DOC_DELEGATION_SIGNATURE', 'Formulaire de délégation de signature', NULL, 'String', 'https://sygal.normandie-univ.fr/fichier/telecharger/permanent/DEMANDE_DELEGATION_SIGNATURE', 2200);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (8, 1, 'DELAI_RETOUR', 'Delai avant le retour des rapports', NULL, 'Number', '14', 1100);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (17, 4, 'SPEC_DATE_BASCULE', 'Spécification pour la date de bascule', 'Spécification pour calculer la date de bascule d''une année universitaire sur la suivante', NULL, '-10 months', 9999);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (18, 4, 'SPEC_ANNEE_UNIV_DATE_DEBUT', 'Spécification de la date de début', 'Spécification de la date de début d''une année universitaire, *fonction de la date de bascule*', NULL, '01/11/%s 00:00:00', 9999);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (5, 1, 'EQUILIBRE_FEMME_HOMME', 'Équilibre Femme/Homme dans le jury', '<p>N''est que indicatif car ne peut &ecirc;tre <em>enforced</em> dans certaines disciplines.</p>', 'String', '0', 400);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (19, 4, 'SPEC_ANNEE_UNIV_DATE_FIN', 'Spécification de la date de fin', 'Spécification de la date de fin d''une année universitaire, *fonction de la date de bascule*', NULL, '31/10/%s 23:59:59', 9999);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (15, 3, 'CAMPAGNE_DEPOT_DEBUT', 'Jour et mois de début de la campagne de dépôt des rapports d''activité pour l''année universitaire N/N+1 en cours. Exemple : 01/04/N+1.', NULL, 'String', '04/05/N+1', 100);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (16, 3, 'CAMPAGNE_DEPOT_FIN', 'Jour et mois de fin de la campagne de dépôt des rapports d''activité pour l''année universitaire N/N+1 en cours. Exemple : 15/06/N+1.', NULL, 'String', '31/06/N+1', 200);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (10, 1, 'DOC_CONFIDENTIALITE', 'Formulaire de demande de confidentialité', NULL, 'String', 'https://sygal.normandie-univ.fr/fichier/telecharger/permanent/DEMANDE_DE_CONFIDENTIALITE', 2500);
INSERT INTO public.unicaen_parametre_parametre (id, categorie_id, code, libelle, description, valeurs_possibles, valeur, ordre) VALUES (14, 2, 'DELAI_ENQUETE', ' Délai pour la saisie de l''enquête (en jours) ', NULL, 'Number', '26', 10);


--
-- Data for Name: unicaen_renderer_macro; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (1, 'Doctorant#Denomination', '<p>Retourne la dénomination du doctorant</p>', 'doctorant', '__toString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (2, 'Module#Libelle', '<p>Retourne le libellé du module de formation</p>', 'module', 'getLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (3, 'Formation#Libelle', '<p>Retourne le libellé de la formation</p>', 'formation', 'getLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (4, 'Session#Modalite', '<p>Retourne la modalité de la formation sous la forme : présentielle ou distancielle.</p>', 'session', 'getModalite');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (5, 'Formation#Responsable', '<p>Retourne la dénomination du responsable de la formation</p>', 'formation', 'toStringResponsable');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (6, 'Session#SeancesTable', '<p>Retourne la liste des séances sous la forme d''un tableau HTML</p>', 'session', 'getSeancesAsTable');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (8, 'Inscription#PositionComplementaire', '<p>Retourne la position sur la liste complémentaire</p>', 'inscription', 'getPositionListeComplementaire');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (9, 'Membre#Denomination', '<p>Retourne la dénomination d''une membre</p>', 'membre', 'getDenomination');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (7, 'Session#Duree', '<p>Retourne la durée totale sous la forme d''un flottant (par exemple : 5,75)</p>', 'session', 'getDuree');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (10, 'These#Discipline', '<p>Affiche le libellé de la discipline associée à la thèse</p>', 'these', 'getLibelleDiscipline');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (12, 'Url#Serment', '<p>Retourne le lien vers le téléchargement du serment du docteur</p>', 'Url', 'getSermentDocteur');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (13, 'Url#ProcesVerbal', '<p>Retourne le lien vers le téléchargement du procés verbal</p>', 'Url', 'getProcesVerbal');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (14, 'Url#RapportSoutenance', '<p>Retourne le lien vers le téléchargement du rapport de soutenance</p>', 'Url', 'getRapportSoutenance');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (18, 'Url#RapportTechnique', '<p>Retourne le lien vers le téléchargement du rapport technique</p>', 'Url', 'getRapportTechnique');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (25, 'These#Titre', NULL, 'these', 'getTitre');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (26, 'Url#RapporteurDashboard', '<p>Fourni l''url vers le darshboard du rapporteur passé en variable</p>', 'Url', 'getUrlRapporteurDashboard');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (27, 'Session#Periode', NULL, 'session', 'getPeriode');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (28, 'Inscription#DureeSuivie', NULL, 'inscription', 'getDureeSuivie');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (29, 'Session#Responsable', NULL, 'session', 'getDenominationResponsable');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (30, 'Signature#EtablissementFormation', NULL, 'Url', 'getFormationSignature');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (31, 'Validation#Date', '<p>Retourne la date de la création de la validation au format d/m/Y à H:i</p>', 'validation', 'getDateToString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (32, 'Validation#Auteur', '<p>Retour le Displayname de l''auteur de la validation</p>', 'validation', 'getAuteurToString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (33, 'Url#SoutenanceProposition', NULL, 'Url', 'getSoutenanceProposition');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (34, 'EcoleDoctorale#Libelle', NULL, 'ecole-doctorale', '__toString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (35, 'Etablissement#Libelle', NULL, 'etablissement', '__toString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (36, 'UniteRecherche#Libelle', NULL, 'unite-recherche', '__toString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (43, 'Url#SoutenancePresoutenance', NULL, 'Url', 'getSoutenancePresoutenance');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (44, 'Acteur#Denomination', '<p>Retourne la dénomination de l''acteur sous la forme  - Prénom Nom -</p>', 'acteur', 'getDenomination');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (45, 'Acteur#Etablissement', '<p>Retourne le libellé de l''établissement du co-encadrant</p>', 'acteur', 'getEtablissementAsLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (46, 'Acteur#Qualite', '<p>retourne la qualité de l''acteur (maître de conférence, directeur de recherche, ...)</p>', 'acteur', 'getQualite');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (48, 'Soutenance#DateRetourRapport', '<p>Affiche la date de retour des rapports</p>', 'soutenance', 'toStringDateRetourRapport');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (49, 'Url#PrerapportSoutenance', '<p>Adresse de téléchargement du pré-rapport de soutenance d''un rapporteur</p>', 'Url', 'getPrerapportSoutenance');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (50, 'Avis#Justification', NULL, 'Avis', 'getMotif');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (51, 'String#ToString', NULL, 'stringelement', 'getTexte');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (52, 'Role#getLibelle', NULL, 'role', 'getLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (53, 'Individu#getDenomination', NULL, 'individu', 'getNomComplet');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (54, 'Soutenance#Date', '<p>Retourne la date de soutenance inscrite dans la proposition</p>', 'soutenance', 'toStringDateSoutenance');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (55, 'Soutenance#Lieu', '<p>Retourne le lieu inscrit dans la proposition</p>', 'soutenance', 'toStringLieu');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (56, 'Soutenance#ModeSoutenance', '<p>Précise le caractère public ou huis clos</p>', 'soutenance', 'toStringPublicOuHuisClos');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (57, 'Url#ConvocationMembre', NULL, 'Url', 'getSoutenanceConvocationMembre');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (58, 'Url#ConvocationDoctorant', NULL, 'Url', 'getSoutenanceConvocationDoctorant');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (59, 'These#Encadrement', NULL, 'these', 'toStringEncadrement');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (60, 'Url#TableauPrerapports', NULL, 'Url', 'generateTablePrerapport');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (61, 'Soutenance#Adresse', NULL, 'soutenance', 'toStringAdresse');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (62, 'Url#Enquete', '<p>Ajoute le lien vers l''enquête</p>', 'Url', 'getUrlEnquete');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (63, 'Admission#Date', '<p>Retourne la date de création d''un dossier d''admission</p>', 'admission', 'getDateToString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (64, 'Admission#Libelle', '', 'admission', '__toString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (65, 'AdmissionOperationAttenduNotification#Anomalies', '<p>Retourne les possibles anomalies rencontrées lors de la création d''une notification Operation Attendue</p>', 'anomalies', 'getAnomalies');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (66, 'AdmissionValidation#Auteur', '<p>Retourne l''auteur de la validation concernant un dossier d''admission</p>', 'admissionValidation', 'getAuteurToString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (67, 'AdmissionValidation#Date', '<p>Retourne la date de la validation concernant un dossier d''admission</p>', 'admissionValidation', 'getDateToString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (68, 'AdmissionValidation#Destructeur', '<p>Retourne le nom/prénom du destructeur de la validation concernant un dossier d''admission</p>', 'admissionValidation', 'getDestructeurToString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (69, 'AdmissionAvis#Auteur', '<p>Retourne l''auteur de l''avis concernant un dossier d''admission</p>', 'admissionAvis', 'getAuteurToString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (70, 'AdmissionAvis#Date', '<p>Retourne la date de l''avis concernant un dossier d''admission</p>', 'admissionAvis', 'getDateToString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (71, 'AdmissionAvis#Destructeur', '<p>Retourne le nom/prénom du destructeur de l''avis concernant un dossier d''admission</p>', 'admissionAvis', 'getDestructeurToString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (72, 'AdmissionAvis#Modificateur', '<p>Retourne le nom/prénom du modificateur de l''avis concernant un dossier d''admission</p>', 'admissionAvis', 'getModificateurToString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (73, 'Individu#Denomination', '', 'individu', 'getNomComplet');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (74, 'Url#Admission', '<p>Permet de récupérer l''url du dossier d''admission d''un étudiant</p>', 'Url', 'getAdmission');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (75, 'TypeValidation#Libelle', '', 'typeValidation', '__toString');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (76, 'AdmissionEtudiant#INE', '<p>Retourne le numéro INE de l''étudiant</p>', 'admissionEtudiant', 'getINE');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (77, 'AdmissionEtudiant#DenominationEtudiant', '<p>Retourne la dénomination de l''étudiant</p>', 'admissionEtudiant', 'getDenominationEtudiant');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (78, 'AdmissionEtudiant#DateNaissance', '<p>Retourne la date de naissance formatée de l''étudiant</p>', 'admissionEtudiant', 'getDateNaissanceFormat');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (79, 'AdmissionEtudiant#VilleNaissance', '<p>Retourne la ville de naissance de l''étudiant</p>', 'admissionEtudiant', 'getVilleNaissance');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (80, 'AdmissionEtudiant#PaysNaissance', '<p>Retourne le pays de naissance de l''étudiant</p>', 'admissionEtudiant', 'getPaysNaissanceLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (81, 'AdmissionEtudiant#Nationalite', '<p>Retourne la nationalité de l''étudiant</p>', 'admissionEtudiant', 'getNationaliteLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (82, 'AdmissionEtudiant#Adresse', '<p>Retourne l''adresse de l''étudiant</p>', 'admissionEtudiant', 'getAdresseLigne3Bvoie');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (83, 'AdmissionEtudiant#CodePostal', '<p>Retourne le code postal de l''adresse de l''étudiant</p>', 'admissionEtudiant', 'getAdresseCodePostal');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (84, 'AdmissionEtudiant#VilleEtudiant', '<p>Retourne la ville de l''étudiant</p>', 'admissionEtudiant', 'getAdresseCodeCommune');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (85, 'AdmissionEtudiant#NumeroTelephone', '<p>Retourne le numéro de téléphone de l''étudiant</p>', 'admissionEtudiant', 'getNumeroTelephone1');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (86, 'AdmissionEtudiant#MailEtudiant', '<p>Retourne le mail de l''étudiant</p>', 'admissionEtudiant', 'getCourriel');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (87, 'AdmissionEtudiant#SituationHandicap', '<p>Retourne la situation de l''handicap de l''étudiant</p>', 'admissionEtudiant', 'getSituationHandicapLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (88, 'AdmissionEtudiant#NiveauEtude', '<p>Retourne le niveau d''étude de l''étudiant</p>', 'admissionEtudiant', 'getNiveauEtudeInformations');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (89, 'AdmissionRecapitulatif#InfosDiplome', '<p>Retourne les informations concernant le diplôme de l''étudiant</p>', 'admissionRecapitulatif', 'getDiplomeIntituleInformationstoHtmlArray');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (90, 'AdmissionInscription#SpecialiteDoctorat', '<p>Retourne la spécialité du doctorat choisie par l''étudiant</p>', 'admissionInscription', 'getSpecialiteDoctoratLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (91, 'AdmissionInscription#ComposanteRattachement', '<p>Retourne la composante choisie pour le doctorat de l''étudiant</p>', 'admissionInscription', 'getComposanteRattachementLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (92, 'AdmissionInscription#EcoleDoctorale', '<p>Retourne l''école doctorale choisie pour le doctorat de l''étudiant</p>', 'admissionInscription', 'getEcoleDoctoraleLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (93, 'AdmissionInscription#UniteRecherche', '<p>Retourne l''unité de recherche choisie pour le doctorat de l''étudiant</p>', 'admissionInscription', 'getUniteRechercheLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (94, 'AdmissionInscription#EtablissementInscription', '<p>Retourne l''établissement d''inscription choisie pour le doctorat de l''étudiant</p>', 'admissionInscription', 'getEtablissementInscriptionLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (95, 'AdmissionInscription#DenominationDirecteurThese', '<p>Retourne la dénomination du directeur de thèse de l''étudiant</p>', 'admissionInscription', 'getDenominationDirecteurThese');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (96, 'AdmissionInscription#MailDirecteurThese', '<p>Retourne le mail du directeur de thèse</p>', 'admissionInscription', 'getEmailDirecteurThese');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (97, 'AdmissionInscription#FonctionDirecteurThese', '<p>Retourne la fonction du directeur de thèse</p>', 'admissionInscription', 'getFonctionDirecteurLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (98, 'AdmissionInscription#TitreThese', '<p>Retourne le titre provisoire de la thèse de l''étudiant</p>', 'admissionInscription', 'getTitreThese');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (99, 'AdmissionInscription#ConfidentialiteSouhaitee', '<p>Retourne la confidentialité voulue pour la thèse de l''étudiant</p>', 'admissionInscription', 'getConfidentialiteSouhaiteeLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (100, 'AdmissionInscription#CotutelleEnvisagee', '<p>Retourne si une cotutelle est envisagée pour la thèse de l''étudiant</p>', 'admissionInscription', 'getCotutelleEnvisageeLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (101, 'AdmissionInscription#CoDirectionDemandee', '<p>Retourne si une codirection est demandée pour la thèse de l''étudiant</p>', 'admissionInscription', 'getCoDirectionDemandeeLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (102, 'AdmissionInscription#UniteRechercheCoDirection', '<p>Retourne l''unité de recherche du co-directeur de thèse pour le doctorat de l''étudiant</p>', 'admissionInscription', 'getUniteRechercheCoDirecteurLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (103, 'AdmissionInscription#EtablissementRattachementCoDirection', '<p>Retourne l''établissement d''inscription du co-directeur de thèse pour le doctorat de l''étudiant</p>', 'admissionInscription', 'getEtablissementRattachementCoDirecteurLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (104, 'AdmissionInscription#FonctionCoDirecteurThese', '<p>Retourne la fonction du co-directeur de thèse</p>', 'admissionInscription', 'getFonctionCoDirecteurLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (105, 'AdmissionInscription#CoEncadrementEnvisage', '<p>Retourne si un co-encadrement est envisagé pour la thèse de l''étudiant</p>', 'admissionInscription', 'getCoEncadrementLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (106, 'AdmissionFinancement#ContratDoctoral', '<p>Retourne si un contrat doctoral est prévu pour la thèse de l''étudiant, et si oui l''employeur est retourné</p>', 'admissionFinancement', 'getContratDoctoralLibelle');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (107, 'AdmissionFinancement#DetailContratDoctoral', '<p>Retourne si les détails du contrat doctoral pour la thèse de l''étudiant</p>', 'admissionFinancement', 'getDetailContratDoctoral');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (108, 'AdmissionFinancement#TempsTravail', '<p>Retourne si les détails du temps de travail pour la thèse de l''étudiant</p>', 'admissionFinancement', 'getTempsTravailInformations');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (109, 'AdmissionFinancement#EstSalarie', '<p>Retourne si le doctorant sera salarié</p>', 'admissionFinancement', 'getEstSalarieInfos');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (110, 'AdmissionFinancement#StatutProfessionnel', '<p>Retourne si les détails du statut professionnel pour la thèse de l''étudiant</p>', 'admissionFinancement', 'getStatutProfessionnel');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (111, 'AdmissionFinancement#EtablissementLaboratoireRecherche', '<p>Retourne si l''établissement hébergeant le laboratoire de recherche</p>', 'admissionFinancement', 'getEtablissementLaboratoireRecherche');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (112, 'AdmissionRecapitulatif#Operations', '<p>Retourne les opérations accordées au dossier d''admission de l''étudiant</p>', 'admissionRecapitulatif', 'getOperationstoHtmlArray');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (113, 'AdmissionConventionFormationDoctorale#InfosCoDirecteur', '<p>Retourne les informations concernant le co-directeur du dossier d''admission de l''étudiant</p>', 'admissionConventionFormationDoctoraleData', 'getCoDirectionInformationstoHtml');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (114, 'AdmissionConventionFormationDoctorale#InfosCoTutelle', '<p>Retourne les informations concernant la co-tutelle du dossier d''admission de l''étudiant</p>', 'admissionConventionFormationDoctoraleData', 'getCoTutelleInformationstoHtml');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (115, 'AdmissionConventionFormationDoctorale#InfosConventionCollaboration', '<p>Retourne les informations concernant la convention de collaboration du dossier d''admission de l''étudiant</p>', 'admissionConventionFormationDoctoraleData', 'getConventionCollaborationInformationstoHtml');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (116, 'AdmissionConventionFormationDoctorale#ResponsablesURDirecteurThese', '<p>Retourne les responsables de l''UR du directeur de thèse du dossier d''admission de l''étudiant</p>', 'admissionConventionFormationDoctoraleData', 'getResponsablesURDirecteurtoHtml');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (117, 'AdmissionConventionFormationDoctorale#ResponsablesURCoDirecteurThese', '<p>Retourne les responsables de l''UR du co-directeur de thèse du dossier d''admission de l''étudiant</p>', 'admissionConventionFormationDoctoraleData', 'getResponsablesURCoDirecteurtoHtml');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (118, 'AdmissionConventionFormationDoctorale#CalendrierPrevisionnel', '<p>Retourne les informations concernant le calendrier prévisionnel de la convention de formation doctorale du dossier d''admission de l''étudiant</p>', 'conventionFormationDoctorale', 'getCalendrierProjetRecherche');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (119, 'AdmissionConventionFormationDoctorale#ModalitesEncadrement', '<p>Retourne les informations concernant les modalités d''encadrement, de suivi de la formation et d''avancement des recherches de la convention de formation doctorale du dossier d''admission de l''étudiant</p>', 'conventionFormationDoctorale', 'getModalitesEncadrSuiviAvancmtRech');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (120, 'AdmissionConventionFormationDoctorale#ConditionsProjRech', '<p>Retourne les informations concernant les conditions matérielles de réalisation du projet de recherche et conditions de sécurité spécifiques de la convention de formation doctorale du dossier d''admission de l''étudiant</p>', 'conventionFormationDoctorale', 'getConditionsRealisationProjRech');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (121, 'AdmissionConventionFormationDoctorale#ModalitesIntegrationUR', '<p>Retourne les informations concernant les modalités d''intégration dans l''unité ou l’équipe de recherche de la convention de formation doctorale du dossier d''admission de l''étudiant</p>', 'conventionFormationDoctorale', 'getModalitesIntegrationUr');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (122, 'AdmissionConventionFormationDoctorale#PartenariatsProjThese', '<p>Retourne les informations concernant les Partenariats impliqués par le projet de thèse de la convention de formation doctorale du dossier d''admission de l''étudiant</p>', 'conventionFormationDoctorale', 'getPartenariatsProjThese');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (123, 'AdmissionConventionFormationDoctorale#MotivationDemandeConfidentialite', '<p>Retourne les informations concernant le calendrier prévisionnel de la convention de collaboration du dossier d''admission de l''étudiant</p>', 'conventionFormationDoctorale', 'getMotivationDemandeConfidentialite');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (124, 'AdmissionConventionFormationDoctorale#ProjetProDoctorant', '<p>Retourne les informations concernant le projet professionnel du doctorant de la convention de collaboration du dossier d''admission de l''étudiant</p>', 'conventionFormationDoctorale', 'getProjetProDoctorant');
INSERT INTO public.unicaen_renderer_macro (id, code, description, variable_name, methode_name) VALUES (125, 'AdmissionConventionFormationDoctorale#Operations', '<p>Retourne les opérations liées  à la convention de formations doctorale accordées au dossier d''admission de l''étudiant</p>', 'admissionConventionFormationDoctoraleData', 'getOperationstoHtmlArray');


--
-- Data for Name: unicaen_renderer_template; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (7, 'FORMATION_SESSION_ANNULEE', '<p>Courrier électronique envoyé aux inscrits des listes principale et complémentaire</p>', 'mail', 'ANNULATION - VAR[Formation#Libelle]', '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>La session de formation VAR[Formation#Libelle] vient d''être annulée.</p>
<p>Cordialement,<br />VAR[Formation#Responsable]</p>', NULL, 'Formation\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (8, 'SOUTENANCE_ENGAGEMENT_IMPARTIALITE', '<p>Texte associé à l''engagement d''impartialité</p>', 'texte', 'Engagement d''impartialité', '<p>En signant cet engagement d''impartialité, je, sous-signé <strong>VAR[Membre#Denomination]</strong>, atteste ne pas avoir de liens d''intérêt, qu''ils soient de nature professionnelle, familiale, personnelle ou patrimoniale avec le doctorant ou son directeur de thèse, ne pas avoir pris part aux travaux de la thèse et ne pas avoir de publication cosignée avec le doctorant dans les cinq dernières années et ne pas avoir participé au comité de suivi de la thèse de VAR[Doctorant#Denomination].</p><p>By signing, I certify that I have no personal or family connection with the doctoral student or his/her PhD supervisor and that I have not taken part in the work of the thesis and not co-authored  publications with the doctoral student for the last five years.<br /><br /></p>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (12, 'TRANSMETTRE_DOCUMENTS_DIRECTION', '<p>Courrier électronique envoyé à la direction de thèse pour transmission de documents avant soutenance</p>', 'mail', 'Transmission des documents pour la soutenance de VAR[Doctorant#Denomination]', '<p>Bonjour,</p>
<p>La soutenance de VAR[Doctorant#Denomination] est imminente.<br />Vous retrouverez ci-dessous les liens pour télécharger les documents utiles pour la soutenance.</p>
<p>Document pour la soutenance :<br />- Serment du docteur : VAR[Url#Serment]<br />- Procès verbal : VAR[Url#ProcesVerbal]<br />- Rapport de soutenance : VAR[Url#RapportSoutenance]<br />- Rapport technique (en cas de viso-conférence) : VAR[Url#RapportTechnique]<br /><br />Bonne journée,<br />L''équipe SyGAL</p>
<p> </p>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (20, 'FORMATION_ATTESTATION', NULL, 'pdf', 'Attestation de suivie de la formation VAR[Formation#Libelle]', '<h1 style="text-align: center;">Attestation de suivi de formation</h1><p></p><p>Bonjour ,</p><p>Je, sousigné·e, certifie que <strong>VAR[Doctorant#Denomination]</strong> a participé à la formation <strong>VAR[Formation#Libelle]</strong> qui s''est déroulée sur la période du VAR[Session#Periode] (Durée : VAR[Session#Duree] heures).</p><p>VAR[Doctorant#Denomination] a suivi VAR[Inscription#DureeSuivie] heure·s de formation.</p><p style="text-align: right;">Le·la responsable du module<br />VAR[Session#Responsable]<br /><br /></p><p style="text-align: right;">VAR[Signature#EtablissementFormation]</p>', NULL, 'Formation\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (21, 'FORMATION_CONVOCATION', NULL, 'pdf', 'Convocation à la formation VAR[Formation#Libelle] du VAR[Session#Periode]', '<h1 style="text-align: center;">Convocation à la session de formation VAR[Formation#Libelle]</h1><p><br /><br />Bonjour VAR[Doctorant#Denomination],</p><p>Nous avons le plaisir de vous informer que la formation, VAR[Formation#Libelle], à laquelle vous êtes inscrit·e se déroulera selon le calendrier ci-dessous :<br />VAR[Session#SeancesTable]<br /><br />En cas d''impossibilité d''assister à tout ou partie de ce stage, merci de bien vouloir informer le ou la responsable du module de formation (VAR[Session#Responsable]) dans les meilleurs délais afin de permettre de contacter un·e doctorant·e actuellement sur liste d''attente.</p><p>Nous vous souhaitons un stage fructueux.</p><p style="text-align: right;">L''application SyGAL,<br />VAR[Signature#EtablissementFormation]</p><p><br /><span style="text-decoration: underline;">P.S.:</span> Cette convocation vaut ordre de mission<br /><br /><br /></p>', 'table { width:100%; } th { text-align:left; }', 'Formation\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (5, 'FORMATION_SESSION_IMMINENTE', NULL, 'mail', 'La session de formation VAR[Formation#Libelle] va bientôt débutée', '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p><br />Nous vous rappelons que la formation VAR[Formation#Libelle]  à laquelle vous êtes inscrit·e va bientôt débuter.</p>
<p>Les séances de cette formation se tiendront :<br />VAR[Session#SeancesTable]</p>
<p><br />Cordialement,<br />VAR[Formation#Responsable]</p>', NULL, 'Formation\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (22, 'DEMANDE_ENGAGEMENT_IMPARTIALITE', '<p>Courrier électronique notifiant un·e futur·e rapporteur·e pour la signature de l''engagement d''impartialité.<br />Envoyé lors de l''appariement membre/acteur</p>', 'mail', 'Demande de signature de l''engagement d''impartialité de la thèse de VAR[Doctorant#Denomination]', '<p>-- Version française ---------------------------------------------------------------</p><p>Bonjour,</p><p>Afin de pouvoir devenir rapporteur de la thèse de <strong>VAR[Doctorant#Denomination]</strong> intitulée <strong>VAR[These#Titre]</strong>, il est nécessaire de signer électroniquement l''engagement d''impartialité dans l''application <em>ESUP-SyGAL</em> :<strong> VAR[Url#RapporteurDashboard]</strong>.<br /><br />Vous accéderez ainsi à une page "index de la soutenance" listant les membres du jury.<br />Cliquez ensuite sur "accès à l’engagement d’impartialité".<br />Puis après avoir pris connaissance des conditions relatives à cet engagement, vous pourrez signer ou non cet engagement d’impartialité.<br />Si vous signez, vous pourrez alors télécharger le PDF du manuscrit de thèse.</p><p>Cordialement<br /><br />-- English version ------------------------------------------------------------------<br /><br />Dear Mrs/Mr,</p><p>Before being officially registered as an external referee for the PhD thesis presented by <strong>VAR[Doctorant#Denomination]</strong> entitled <strong>VAR[These#Titre]</strong>, you have to sign the "impartiality commitment" available in your dashborad : <strong>VAR[Url#RapporteurDashboard]</strong>.<br /><br />You will then be connected to a web page entitled "index of the PhD defense" listing the PhD jury members.<br />Click then on "access to the impartiality commitment".<br />Then, after reading the requirements regarding the impartiality commitment of an external referee, you sign it or not.<br />If you sign it, you will be able to download the PDF version of the PhD manuscript.</p><p>Best regards,</p><p>-- Justification ----------------------------------------------------------------------<br /><br />Vous avez reçu ce mail car :</p><ul><li>vous avez été désigné rapporteur pour la thèse de VAR[Doctorant#Denomination]</li><li>la signature a été annulée<br /><br /></li></ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (24, 'REFUS_ENGAGEMENT_IMPARTIALITE', '<p>Courrier électronique envoyé lors du refus de l''engagement d''impartialité</p>', 'mail', 'Refus de l''engagement d''impartialité de la thèse de VAR[Doctorant#Denomination] par VAR[Membre#Denomination]', '<p>Bonjour,</p><p><strong>VAR[Membre#Denomination]</strong> vient de refuser l''engagement d''impartialité de la thèse de <strong>VAR[Doctorant#Denomination]</strong> intitulée <strong>VAR[These#Titre]</strong>.</p><p>-- Justification ----------------------------------------------------------------------</p><p>Vous avez reçu ce mail car :</p><ul><li>le rapporteur VAR[Membre#Denomination] vient de refuser de signer l''engagement d''impartialité;</li><li>vous êtes :<ul><li>soit un des acteurs directs de la thèse de VAR[Doctorant#Denomination],</li><li>soit un gestionnaire de la maison du doctorat de l''établissement d''inscription du doctorant.<br />            <br /><br /></li></ul></li></ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (25, 'ANNULATION_ENGAGEMENT_IMPARTIALITE', '<p>Courrier électronique envoyé lors de l''annulation d''un engagement d''impartialité</p>', 'mail', 'Annulation de la signature de l''engagement d''impartialité de VAR[Membre#Denomination] pour la thèse de VAR[Doctorant#Denomination]', '<p>Bonjour,</p><p><br />Votre signature de l''engagement d''impartialité de la thèse de <strong>VAR[Doctorant#Denomination]</strong> intitulée <strong>VAR[These#Titre]</strong> vient d''être annulée.</p> <p>-- Justification ----------------------------------------------------------------------</p> <p>Vous avez reçu ce mail car :</p><ul><li>vous avez signé l''engagement d''impartialité pour la thèse de VAR[Doctorant#Denomination];  </li><li>la signature a été annulée. </li></ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (26, 'SOUTENANCE_VALIDATION_ANNULEE', '<p>Annulation de la validation</p>', 'mail', 'Votre validation de la proposition de soutenance de VAR[Doctorant#Denomination] a été annulée', '<p>Bonjour,</p><p>Ceci est un mail envoyé automatiquement par l''application ESUP SyGAL</p><p>Suite à la modification de la proposition de soutenance, votre validation (faite le VAR[Validation#Date]) a été annulée. Si la nouvelle proposition vous convient, veuillez valider la proposition de soutenance à nouveau.<br /><br />Pour consulter, les modifications faites connectez-vous à  ESUP SyGAL et visualisez la proposition de soutenance en utilisant le lien suivant : VAR[Url#SoutenanceProposition].</p><p><span style="text-decoration: underline;">NB :</span> La proposition de soutenance sera envoyée automatiquement à votre unité de recherche puis à votre école doctorale, une fois que tous les intervenants directs auront validé celle-ci (c.-à-d. doctorant, directeur et co-directeur(s)).<br /><br />-- Justification ----------------------------------------------------------------------</p><p>Vous avez reçu ce mail car :</p><ul><li>vous avez validé la proposition de soutenance de VAR[Doctorant#Denomination] ;</li><li>une modification de la proposition a été faite ou demandée.</li></ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (27, 'SOUTENANCE_VALIDATION_ACTEUR_DIRECT', '<p>Mail de validation d''une proposition par un des acteurs directs</p>', 'mail', 'Une validation de votre proposition de soutenance vient d''être faite', '<p>Bonjour,</p><p>Ceci est un mail envoyé automatiquement par l''application ESUP SyGAL.</p><p>VAR[Validation#Auteur] vient de valider la proposition de soutenance de thèse.</p><p><br />Pour consulter cette proposition, connectez-vous à ESUP SyGAL et visualisez la proposition de soutenance en utilisant le lien suivant : VAR[Url#SoutenanceProposition].</p><p><span style="text-decoration: underline;">NB :</span> La proposition de soutenance sera envoyée automatiquement à votre unité de recherche puis à votre école doctorale, une fois que tous les intervenants directs auront validé celle-ci (c.-à-d. doctorant, directeur et co-directeur(s)).</p><p>-- Justification ----------------------------------------------------------------------</p><p>Vous avez reçu ce mail car :</p><ul><li>un des acteurs directs de la thèse de VAR[Doctorant#Denomination] vient de valider la proposition de soutenance  ;</li><li>vous êtes un des acteurs directs de la thèse de VAR[Doctorant#Denomination].</li></ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (28, 'SOUTENANCE_VALIDATION_DEMANDE_UR', NULL, 'mail', 'Demande de validation d''une proposition de la soutenance de VAR[Doctorant#Denomination]', '<p>Bonjour,</p><p>Ceci est un mail envoyé automatiquement par l''application ESUP SyGAL.</p><p>Une proposition de soutenance vient d''être faite pour la thèse suivante :</p><table style="width: 473.433px;"><tbody><tr><td style="width: 547px;"><strong>Titre</strong></td><td style="width: 467.433px;">VAR[These#Titre]</td></tr><tr><td style="width: 547px;"><strong>Doctorant·e</strong></td><td style="width: 467.433px;">VAR[Doctorant#Denomination]</td></tr></tbody></table><p>Pour examiner cette proposition et statuer sur celle-ci merci de vous rendre dans l''application ESUP SyGAL : VAR[Url#SoutenanceProposition].<br /><br />-- Justification ----------------------------------------------------------------------</p><p> Vous avez reçu ce mail car :</p><ul><li>tous les acteurs directs de la thèse de VAR[Doctorant#Denomination] ont validé la proposition de soutenance ;</li><li>vous êtes un·e responsable de l''unité de recherche encadrant la thèse.</li></ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (29, 'SOUTENANCE_VALIDATION_DEMANDE_ED', NULL, 'mail', 'Demande de validation d''une proposition de la soutenance de VAR[Doctorant#Denomination]', '<p>Bonjour,</p><p>Ceci est un mail envoyé automatiquement par l''application ESUP SyGAL.</p><p>Une proposition de soutenance vient d''être faite pour la thèse suivante :</p><table style="width: 473.433px;"><tbody><tr><td style="width: 547px;"><strong>Titre</strong></td><td style="width: 467.433px;">VAR[These#Titre]</td></tr><tr><td style="width: 547px;"><strong>Doctorant·e</strong></td><td style="width: 467.433px;">VAR[Doctorant#Denomination]</td></tr></tbody></table><p>Pour examiner cette proposition et statuer sur celle-ci merci de vous rendre dans l''application ESUP SyGAL : VAR[Url#SoutenanceProposition].<br /><br />-- Justification ----------------------------------------------------------------------</p><p> Vous avez reçu ce mail car :</p><ul><li>l''unité de recherche de la thèse de VAR[Doctorant#Denomination] ont validé la proposition de soutenance ;</li><li>vous êtes un·e responsable de l''école doctorale encadrant la thèse.</li></ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (30, 'SOUTENANCE_VALIDATION_DEMANDE_ETAB', NULL, 'mail', 'Demande de validation d''une proposition de la soutenance de VAR[Doctorant#Denomination]', '<p>Bonjour,</p><p>Ceci est un mail envoyé automatiquement par l''application ESUP SyGAL.</p><p>Une proposition de soutenance vient d''être faite pour la thèse suivante :</p><table style="width: 473.433px;"><tbody><tr><td style="width: 547px;"><strong>Titre</strong></td><td style="width: 467.433px;">VAR[These#Titre]</td></tr><tr><td style="width: 547px;"><strong>Doctorant·e</strong></td><td style="width: 467.433px;">VAR[Doctorant#Denomination]</td></tr></tbody></table><p>Pour examiner cette proposition et statuer sur celle-ci merci de vous rendre dans l''application ESUP SyGAL : VAR[Url#SoutenanceProposition].<br /><br />-- Justification ----------------------------------------------------------------------</p><p> Vous avez reçu ce mail car :</p><ul><li>l''école doctorale de la thèse de VAR[Doctorant#Denomination] ont validé la proposition de soutenance ;</li><li>vous êtes un·e responsable de la maison du doctorat encadrant la thèse.</li></ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (1, 'FORMATION_INSCRIPTION_ENREGISTREE', '<p>Mail envoyé au doctorant·e lors d''une inscription à une session de formation</p>', 'mail', 'Validation de votre inscription à la session de formation VAR[Formation#Libelle]', '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>Nous avons bien reçu votre demande d’inscription à la formation VAR[Formation#Libelle] se déroulant : <br />VAR[Session#SeancesTable]</p>
<p><br />Cordialement,<br />VAR[Formation#Responsable]<br /><br style="background-color: #2b2b2b; color: #a9b7c6; font-family: ''JetBrains Mono'',monospace; font-size: 9,8pt;" /></p>', NULL, 'Formation\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (3, 'FORMATION_INSCRIPTION_LISTE_COMPLEMENTAIRE', NULL, 'mail', 'Vous êtes sur la liste complémentaire de la formation VAR[Formation#Libelle]', '<p>Bonjour VAR[Doctorant#Denomination],<br /><br />Vous êtes inscrit·e en <strong>liste complémentaire</strong> de la session de formation VAR[Formation#Libelle].<br />Vous êtes à la position VAR[Inscription#PositionComplementaire] sur cette liste.</p>
<p><br />La session de formation se déroulera selon les dates suivantes :<br />VAR[Session#SeancesTable]<br />Si une place en liste principale se libère vous serez informé·e par l''application ESUP-SyGAL.<br /><br />Cordialement,<br />VAR[Formation#Responsable]</p>', NULL, 'Formation\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (6, 'FORMATION_SESSION_TERMINEE', NULL, 'mail', 'La session de formation VAR[Formation#Libelle] est maintenant terminée.', '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>Nous espérons que la formation s’est bien déroulée.<br />Pour l’obtention de l’attestation de VAR[Session#Duree] heures de formation , il est nécessairement de remplir le questionnaire de satisfaction sur ESUP-SyGAL.</p>
<p>Cordialement,<br />VAR[Formation#Responsable]</p>', NULL, 'Formation\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (2, 'FORMATION_INSCRIPTION_LISTE_PRINCIPALE', '<p>Mail envoyer à l''étudiant lorsqu''il est inscrit en liste principale</p>', 'mail', 'Vous êtes sur la liste principale de la formation VAR[Formation#Libelle]', '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>Vous êtes inscrit·e en <strong>liste principale</strong> de la session de formation VAR[Formation#Libelle].<br />Celle-ci se déroulera selon les dates suivantes :<br />VAR[Session#SeancesTable]<br />Pensez à bien réserver cette date dans votre agenda.</p>
<p>Si vous avez besoin d''une convocation, vous pouvez retrouver celle-ci dans ESUP-SyGAL dans la partie ''<em>Mes formations</em>'' onglet ''<em>Mes inscriptions en cours</em>''.</p>
<p>En cas d’empêchement pensez à vous désinscrire afin de libérer votre place pour une personne de la liste complémentaire.</p>
<p>Cordialement,<br />VAR[Formation#Responsable]<br /><br /></p>', NULL, 'Formation\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (9, 'SERMENT_DU_DOCTEUR', '<p>Texte du serment du doctorant</p>', 'pdf', 'serment_du_docteur.pdf', '<p>Version Française :<br />« En présence de mes pairs.<br /> Parvenu(e) à l''issue de mon doctorat en VAR[These#Discipline], et ayant ainsi pratiqué, dans ma quête du savoir, l''exercice d''une recherche scientifique exigeante, en cultivant la rigueur intellectuelle, la réflexivité éthique et dans le respect des principes de l''intégrité scientifique, je m''engage, pour ce qui dépendra de moi, dans la suite de ma carrière professionnelle quel qu''en soit le secteur ou le domaine d''activité, à maintenir une conduite intègre dans mon rapport au savoir, mes méthodes et mes résultats. »<br /><br />English version :<br />« In the presence of my peers.<br /> With the completion of my doctorate in VAR[These#Discipline], in my quest for knowledge, I have carried out demanding research, demonstrated intellectual rigour, ethical reflection, and respect for the principles of research integrity. As I pursue my professional career, whatever my chosen field, I pledge, to the greatest of my ability, to continue to maintain integrity in my relationship to knowledge, in my methods and in my results »<br /><br /></p>
<p><br class="Apple-interchange-newline" /><br /></p>
<p> </p>
<p> </p>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (41, 'FORMATION_SESSION_IMMINENTE_FORMATEUR', '<p>Courrier électronique envoyé aux formateur·trices lorsque la session est imminente</p>', 'mail', 'La session de formation VAR[Formation#Libelle] va bientôt débutée', '<p>Bonjour,</p> <p>Nous vous rappelons que la formation VAR[Formation#Libelle] dont vous êtes déclaré·e comme formateur·trice va bientôt débuter.<br /><br />Les séances de cette formation se tiendront :<br />VAR[Session#SeancesTable]</p> <p>Cordialement,<br />VAR[Formation#Responsable]</p>', NULL, 'Formation\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (44, 'COENCADREMENTS_JUSTIFICATIF', NULL, 'pdf', 'Justificatif de co-encadrements de VAR[Acteur#Denomination]', '<h1>Justificatif de co-encadrements</h1>
<p>Ce document certifie que VAR[Acteur#Denomination], actuellement VAR[Acteur#Qualite] à VAR[Acteur#Etablissement], a assuré la fonction de co-encadrant sur pour les thèses suivantes :<br />###LISTING_THESE###</p>', NULL, 'These\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (43, 'VALIDATION_SOUTENANCE_ENVOI_PRESOUTENANCE', '<p>Courrier électronique pour indiquer que la thèse peut débuter le circuit de présoutenance</p>', 'mail', 'Vous pouvez maintenant procéder au renseignement des informations liées à la soutenance de VAR[Doctorant#Denomination]', '<p>Bonjour,</p>
<p>La proposition de soutenance de la thèse suivante a été totalement validée :</p>
<table>
<tbody>
<tr>
<th>Titre :</th>
<td>VAR[These#Titre]</td>
</tr>
<tr>
<th>Doctorant :</th>
<td>VAR[Doctorant#Denomination]</td>
</tr>
</tbody>
</table>
<p>Vous pouvez maintenance procéder à la saisie des informations liées à la soutenance : VAR[Url#SoutenancePresoutenance]</p>
<p>---------------------------------</p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>la proposition de soutenance de thèse de VAR[Doctorant#Denomination] a été complètement validée</li>
<li>vous êtes gestionnaire de la maison du doctorat de l''établissement d''inscription du doctorant.</li>
</ul>', 'table { width:100%; } th { text-align:left; }', 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (42, 'VALIDATION_SOUTENANCE_AVANT_PRESOUTENANCE', '<p>Courrier électronique indiquant aux acteurs directs et aux structures que le dossiers est complet et part pour saisie en présoutenance</p>', 'mail', 'Validation de proposition de soutenance de VAR[Doctorant#Denomination]', '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.<br /><br />La proposition de soutenance de thèse suivante a été validée par tous les acteurs et structures associées :</p>
<table>
<tbody>
<tr>
<th>Titre :</th>
<td>VAR[These#Titre]</td>
</tr>
<tr>
<th>Doctorant :</th>
<td>VAR[Doctorant#Denomination]</td>
</tr>
</tbody>
</table>
<p>Pour examiner cette proposition merci de vous rendre dans l''application ESUP-SyGAL : VAR[Url#SoutenanceProposition].</p>
<p>-----------------------</p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>la proposition de soutenance vient d''être validée par tous les acteurs directs et toutes les structures concernées ;</li>
<li>vous êtes soit :
<ul>
<li>un des acteurs directs de la thèse de VAR[Doctorant#Denomination]</li>
<li>un·e responsable de l''école de doctorale gérant la thèse,</li>
<li>un·e responsable de l''unité de recherche encadrant la thèse,</li>
<li>un·e gestionnaire du bureau des doctorat de l''établissement d''inscription du doctorant. <br /><br /></li>
</ul>
</li>
</ul>', 'table { width:100%; } th { text-align:left; }', 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (48, 'SOUTENANCE_AVIS_FAVORABLE', NULL, 'mail', 'VAR[Acteur#Denomination] vient de rendre un avis favorable pour la thèse de VAR[Doctorant#Denomination]', '<p>Bonjour,</p>
<p>Le rapporteur VAR[Acteur#Denomination] vient de rendre un avis favorable pour la thèse de VAR[Doctorant#Denomination] intitulée VAR[These#Titre].</p>
<p>Vous pouvez consulter le rapport de pré-soutenance en allant sur la page de gestion de la soutenance VAR[Url#SoutenanceProposition] ou en utilisante le lien suivant : VAR[Url#PrerapportSoutenance]</p>
<p>Vous avez reçu ce courrier électronique car :</p>
<ul>
<li>un avis favorable a été rendu par un rapporteur</li>
<li>vous êtes soit :
<ul>
<li>un·e gestionnaire de la maison du doctorat de l''établissement d''inscription du doctorant</li>
<li>un·e responsable de l''école doctorale gérant la thèse de VAR[Doctorant#Denomination]</li>
<li>un·e responsable de l''unité de recherche accueillant la thèse de VAR[Doctorant#Denomination]</li>
<li>une personne participant à la direction de la thèse de VAR[Doctorant#Denomination]</li>
</ul>
</li>
</ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (49, 'SOUTENANCE_AVIS_DEFAVORABLE', NULL, 'mail', 'VAR[Acteur#Denomination] vient de rendre un avis défavorable pour la thèse de VAR[Doctorant#Denomination]', '<p>Bonjour,</p>
<p>Le rapporteur VAR[Acteur#Denomination] vient de rendre un avis défavorable pour la thèse de VAR[Doctorant#Denomination] intitulée VAR[These#Titre].<br />VAR[Acteur#Denomination] motive ce refus avec le justification suivante : VAR[Avis#Justification]</p>
<p> </p>
<p>Vous pouvez consulter le rapport de pré-soutenance en allant sur la page de gestion de la soutenance VAR[Url#SoutenanceProposition] ou en utilisante le lien suivant : VAR[Url#PrerapportSoutenance]</p>
<p>Vous avez reçu ce courrier électronique car :</p>
<ul>
<li>un avis défavorable a été rendu par un rapporteur</li>
<li>vous êtes soit :
<ul>
<li>un·e gestionnaire de la maison du doctorat de l''établissement d''inscription du doctorant</li>
<li>un·e responsable de l''école doctorale gérant la thèse de VAR[Doctorant#Denomination]</li>
<li>un·e responsable de l''unité de recherche accueillant la thèse de VAR[Doctorant#Denomination]</li>
<li>une personne participant à la direction de la thèse de VAR[Doctorant#Denomination]</li>
</ul>
</li>
</ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (50, 'PROPOSITION_REFUS', '<p>Courrier électronique envoyé aux acteurs directs de la thèse lors du refus de la proposition</p>', 'mail', 'Votre proposition de soutenance a été réfusée', '<p>Bonjour,</p>
<p>VAR[Individu#getDenomination] (VAR[Role#getLibelle] <em>VAR[Etablissement#Libelle]</em>) a refusé votre proposition de soutenance.<br />Le motif du refus est le suivant :</p>
<table>
<tbody>
<tr>
<td>VAR[String#ToString]</td>
</tr>
</tbody>
</table>
<p>Suite à ce refus toutes les validations associées à cette proposition ont été annulées.<br /><br />Vous avez reçu ce mail car :</p>
<ul>
<li>la proposition de soutenance de thèse de VAR[Doctorant#Denomination] a été refusée ;</li>
<li>vous êtes un des acteurs directs de la thèse.<br /><br /></li>
</ul>', 'td {border: 1px solid black;}', 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (51, 'SOUTENANCE_TOUS_AVIS_RENDUS', '<p>Courrier électronique vers les <em>aspects doctorals</em>  indiquant que tous les avis ont été rendus</p>', 'mail', 'Tous les avis de soutenance de la thèse de VAR[Doctorant#Denomination] ont été rendus.', '<p>Bonjour,</p>
<p>Les rapporteurs de la thèse de VAR[Doctorant#Denomination] ont rendu leur rapport de pré-soutenance.</p>
<p>Vous pouvez les consulter sur la page de gestion de la pré-soutenance de cette thèse : VAR[Url#SoutenancePresoutenance]<br /><br />Vous avez reçu ce mail car :</p>
<ul>
<li>tous les avis de soutenance de la thèse de VAR[Doctorant#Denomination] ont été rendus ;</li>
<li>vous êtes gestionnaire de la maison du doctorat de l''établissement d''inscription du doctorant</li>
</ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (46, 'DEMANDE_RAPPORT_SOUTENANCE', '<p>Courrier électronique de demande du rapport de présoutenance à un·e rapporteur·trice</p>', 'mail', 'Demande de rapport de présoutenance pour la thèse de VAR[Doctorant#Denomination]', '<p><em>-- Version française -----------------------------------------------------------</em><br /><br />Bonjour,</p>
<p>Vous venez d''être désigné comme rapporteur pour la thèse de VAR[Doctorant#Denomination] intitulée <strong>VAR[These#Titre]</strong>.</p>
<p>Ce courrier électronique a pour but de vous rappeler que vous devez rendre votre avis de soutenance pour la date du <strong>VAR[Soutenance#DateRetourRapport]</strong>.<br />Pour rendre votre avis, connectez-vous à l''application ESUP-SyGAL en utilisant l''adresse suivante : VAR[Url#RapporteurDashboard].</p>
<p>Si vous êtes favorable à cette soutenance, nous vous remercions d''indiquer, à la fin de votre rapport, la mention suivante : « Par conséquent, je suis favorable à la soutenance de thèse de VAR[Doctorant#Denomination] pour obtenir le grade de Docteur de l''VAR[Etablissement#Libelle] et de Normandie Université.</p>
<p> Cordialement,</p>
<p><em>--  English version -----------------------------------------------------------</em></p>
<p><br />Dear Mrs/Mr,</p>
<p>You have been appointed as an external referee for the PhD thesis presented by VAR[Doctorant#Denomination] entitled <strong>VAR[These#Titre]</strong>.</p>
<p>This mail is a reminder that you have until <strong>VAR[Soutenance#DateRetourRapport]</strong> to submit your report.<br />To do so you can use the following link : VAR[Url#RapporteurDashboard].</p>
<p>For these reasons, I am in favor of VAR[Doctorant#Denomination]''s thesis defense ; and thus for VAR[Doctorant#Denomination] to be able to claim the title of doctor from VAR[Etablissement#Libelle] and Normandie Université.</p>
<p>Best regards,</p>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (52, 'SOUTENANCE_TOUS_AVIS_RENDUS_DIRECTION', '<p>Courrier électronique adressé aux encadrants d''une thèse lorsque tous les avis sont rendus.</p>', 'mail', 'Tous les avis de soutenance de la thèse de VAR[Doctorant#Denomination] ont été rendus.', '<p>Bonjour,</p>
<p>Les rapporteurs de la thèse de VAR[Doctorant#Denomination] ont rendu leur rapport de pré-soutenance.</p>
<p>Vous pouvez les consulter ceux-ci sur la page de la proposition de soutenance : VAR[Url#SoutenanceProposition]<br /><br />Vous avez reçu ce mail car :</p>
<ul>
<li>tous les avis de soutenance de la thèse de VAR[Doctorant#Denomination] ont été rendus ;</li>
<li>vous êtes membre de la direction de la thèse de VAR[Doctorant#Denomination]. <br /><br /></li>
</ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (53, 'DEMANDE_PRERAPPORT', '<p>Courrier électronique envoyé au rapport pour leur demander leur pré-rapport de soutenance</p>', 'mail', 'Demande de l''avis de soutenance de la thèse de VAR[Doctorant#Denomination]', '<p><em>-- Version française----------------------------------------------------</em><br /><br />Bonjour,</p>
<p>Vous pouvez commencer à examiner les travaux thèse de <strong>VAR[Doctorant#Denomination]</strong> ayant pour titre VAR[These#Titre].</p>
<p>Par la suite, vous devez établir un rapport incluant votre avis sur la soutenance et votre signature au moins 14 jours avant la date de soutenance, puis le déposer sur la plateforme ESUP-SyGAL à l’adresse suivante : VAR[Url#RapporteurDashboard].</p>
<p>Si votre avis est négatif, pourriez-vous indiquer brièvement les raisons de ce choix puis déposer votre rapport.</p>
<p><span style="text-decoration: underline;">Rappel :</span> <strong>Le rapport est attendu pour le VAR[Soutenance#DateRetourRapport].</strong><br />Au delà de cette date, vous ne pourrez plus rendre votre rapport.</p>
<p>Cordialement,</p>
<p><em>-- English version -----------------------------------------------------</em><br /><br />Dear Mrs/Mr,</p>
<p>Since you have signed the "impartiality commitment", you can start the evaluation of the PhD thesis presented by <strong>VAR[Doctorant#Denomination]</strong>, entitled VAR[These#Titre].<br />Then, you must give your opinion about the PhD thesis and upload your signed PhD report at least 14 days before the date of the PhD defense at : VAR[Url#RapporteurDashboard].</p>
<p>In case of a negative opinion, please indicate briefly the main reasons for the rejection and upload your signed PhD report.</p>
<p><strong>Keep in mind that your report must be uploaded before VAR[Soutenance#DateRetourRapport].</strong><br />After the deadline, you won''t be able to upload your report.</p>
<p>Best regards,</p>
<p> </p>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (55, 'SOUTENANCE_CONVOCATION_DOCTORANT', NULL, 'mail', 'Convocation pour la soutenance de thèse de VAR[Doctorant#Denomination]', '<p>Bonjour,</p>
<p>Par décision en date du VAR[Validation#Date], vous avez été autorisé·e à présenter en soutenance vos travaux en vue de l''obtention du diplôme : Doctorat en VAR[These#Discipline].<br /><br />La soutenance aura lieu le VAR[Soutenance#Date] à l''adresse suivante : <br />VAR[Soutenance#Adresse]<br /><br />La soutenance VAR[Soutenance#ModeSoutenance].<br /><br />Vous pouvez accéder aux pré-rapports de soutenance sur la page de la proposition de soutenance : VAR[Url#SoutenanceProposition]<br /><br />Nous vous prions d''agréer, l''expression de nos salutations distinguées.<br /><br /><span style="text-decoration: underline;">P.S.:</span> Vous pouvez obtenir une version imprimable de cette convocation à l''adresse suivante : VAR[Url#ConvocationDoctorant]<br /><br /></p>
<p><em>-- Justification -------------------------------------------------------------------------------</em></p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>votre proposition de soutenance a été validé par la maison du doctorat; </li>
<li>vous êtes le doctorant associé à la proposition de soutenance. </li>
</ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (23, 'SIGNATURE_ENGAGEMENT_IMPARTIALITE', '<p>Courrier électronique envoyé vers les maisons du doctorat lors de la signature de l''engagement d''impartialité par un rapporteur</p>', 'mail', 'Signature de l''engagement d''impartialité de la thèse de VAR[Doctorant#Denomination] par VAR[Membre#Denomination]', '<p>Bonjour,</p>
<p><strong>VAR[Membre#Denomination]</strong> vient de signer l''engagement d''impartialité de la thèse de <strong>VAR[Doctorant#Denomination]</strong> intitulée <strong>VAR[These#Titre]</strong>.</p>
<p>-- Justification ----------------------------------------------------------------------<br /><br />Vous avez reçu ce mail car :</p>
<ul>
<li>le rapporteur VAR[Membre#Denomination] vient de signer l''engagement d''impartialité;</li>
<li>vous êtes un gestionnaire de la maison du doctorat de l''établissement d''inscription du doctorant . <br /><br /></li>
</ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (54, 'SOUTENANCE_FEU_VERT', NULL, 'mail', 'La soutenance de VAR[Doctorant#Denomination] a été acceptée par votre établissement.', '<p>Bonjour,</p>
<p>La soutenance de VAR[Doctorant#Denomination] a été acceptée par votre établissement.<br />Conformément aux informations que vous avez fournies celle-ci se tiendra le VAR[Soutenance#Date] dans VAR[Soutenance#Lieu].</p>
<p>Vous pouvez consulter les rapports de pré-soutenance en bas de la page de la proposition de soutenance : VAR[Url#SoutenanceProposition]</p>
<p><em>-- Justification ---------------------------------------------------------------------------</em></p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>la maison du doctorat vient de donner son feu vert pour la soutenance</li>
<li>vous êtes soit :<br />
<ul>
<li>un·e acteur·trice direct·e de la thèse de  VAR[Doctorant#Denomination] ;</li>
<li>un·e responsable de l''école de doctorale gérant la thèse ;</li>
<li>un·e responsable de l''unité de recherche encadrant la thèse.</li>
</ul>
</li>
</ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (56, 'SOUTENANCE_CONVOCATION_MEMBRE', NULL, 'mail', 'Convocation pour la soutenance de thèse de VAR[Doctorant#Denomination]', '<p>Bonjour,</p>
<p>Par décision en date du VAR[Validation#Date], le chef de l''établissement VAR[Etablissement#Libelle] vous a désigné·e pour participer au jury devant examiner les travaux de VAR[Doctorant#Denomination] en vue de l''obtention du diplôme : Doctorat en VAR[These#Discipline].</p>
<p>Le titre des travaux est : VAR[These#Titre]<br /><br /><br />Les travaux sont dirigés par VAR[These#Encadrement]</p>
<p>La soutenance aura lieu le VAR[Soutenance#Date] à l''adresse suivante :<br />VAR[Soutenance#Adresse]</p>
<p>La soutenance est encadrée par VAR[Soutenance#ModeSoutenance].</p>
<p>Vous pouvez accéder aux rapports de pré-soutenance grâce aux liens suivants :<br />VAR[Url#TableauPrerapports]<br /><br />Je vous prie d''agréer, l''expression de mes salutations distinguées.<br /><br />P.S.: Vous pouvez obtenir une version imprimable de cette convocation à l''adresse suivante : VAR[Url#ConvocationMembre]<br /><br /><em>-- Justification -----------------------------------------------------------------</em></p>
<p>Vous avez reçu ce mail car :</p>
<ul>
<li>la proposition de soutenance de VAR[Doctorant#Denomination] a été validée; </li>
<li>vous avez été désigné comme membre du jury pour la thèse de VAR[Doctorant#Denomination].</li>
</ul>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (47, 'CONNEXION_RAPPORTEUR', '<p>Courrier électronique envoyé aux rapporteur·trices  pour la connexion à SyGAL</p>', 'mail', 'Connexion en tant que rapporteur de la thèse de VAR[Doctorant#Denomination]', '<p><em>-- Version française----------------------------------------------------</em><br /><br />Bonjour,</p>
<p>Vous venez d''être désigné comme rapporteur pour la thèse de VAR[Doctorant#Denomination] intitulée <strong>VAR[These#Titre].</strong> Afin de pouvoir vous connecter à l''application ESUP-SyGAL et ainsi pouvoir rendre votre avis de pré-soutenance, vous pouvez utiliser l''adresse suivante : VAR[Url#RapporteurDashboard].<br /><br />Vous avez jusqu''au <strong>VAR[Soutenance#DateRetourRapport]</strong> pour effectuer ce dépôt.</p>
<p>Si vous êtes favorable à cette soutenance, nous vous remercions d''indiquer, à la fin de votre rapport, la mention suivante : « Par conséquent, je suis favorable à la soutenance de thèse de VAR[Doctorant#Denomination] pour obtenir le grade de Docteur de l''VAR[Etablissement#Libelle] et de Normandie Université.</p>
<p> <br />Cordialement,</p>
<p><br /><em>-- English version -----------------------------------------------------</em><br /><br />Dear Mrs/Mr,</p>
<p>You have been appointed as an external referee for the PhD thesis presented by VAR[Doctorant#Denomination] entitled <strong>VAR[These#Titre]</strong>. In order to have access to ESUP-SyGAL  web-based application for submitting your report, you can use the following link : VAR[Url#RapporteurDashboard].</p>
<p>You have until the <strong>VAR[Soutenance#DateRetourRapport]</strong> to upload your report.</p>
<p>For these reasons, I am in favor of VAR[Doctorant#Denomination]''s thesis defense ; and thus for VAR[Doctorant#Denomination] to be able to claim the title of doctor from VAR[Etablissement#Libelle] and Normandie Université.<br /><br /></p>
<p>Best regards,<br /><br /><br /></p>
<p> </p>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (57, 'ADMISSION_OPERATION_ATTENDUE', '<p>Mail pour notifier les acteurs de la prochaine opération attendue du dossier d''admission</p>', 'mail', 'Dossier d''admission de VAR[Individu#Denomination]', '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL. </p>
<p><em>VAR[AdmissionOperationAttenduNotification#Anomalies]</em></p>
<p>Le <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> est en attente de l''opération suivante de votre part : <strong>VAR[TypeValidation#Libelle]</strong>.</p>
<p>Merci de vous connecter sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission]</p>
<p><strong>Note importante : une fois connecté, pensez à vérifier le rôle que vous endossez (en cliquant sur votre nom en haut à droite des pages de l''application) et le cas échéant à sélectionner celui permettant de réaliser l''opération attendue.</strong></p>', NULL, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (58, 'ADMISSION_VALIDATION_AJOUTEE', '<p>Mail pour notifier qu''une validation a été ajoutée au dossier d''admission</p>', 'mail', 'Dossier d''admission de VAR[Individu#Denomination] ', '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>Le <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> a été <strong>validé</strong> par VAR[AdmissionValidation#Auteur], le VAR[AdmissionValidation#Date]</p>', NULL, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (59, 'ADMISSION_NOTIFICATION_GESTIONNAIRE', '<p>Envoi d''un mail à l''initiative de l''étudiant, afin de notifier le(s) gestionnaire(s) que le dossier est prêt à être vérifié</p>', 'mail', 'La saisie du dossier d''admission de VAR[Individu#Denomination] est terminée', '<p>Bonjour,</p>
<p>VAR[Individu#Denomination] a terminé la saisie de son dossier d''admission.</p>
<p>Merci de prendre connaissance des informations saisies en vous connectant sur la plateforme ESUP-SyGAL via le lien : VAR[Url#Admission].</p>', NULL, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (60, 'ADMISSION_VALIDATION_SUPPRIMEE', '<p>Mail pour notifier qu''une validation a été supprimée du dossier d''admission</p>', 'mail', 'Dossier d''admission de VAR[Individu#Denomination] dévalidé', '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>La <strong>VAR[TypeValidation#Libelle]</strong> du <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> datant du VAR[AdmissionValidation#Date] a été <strong>annulée </strong>VAR[AdmissionValidation#Destructeur]</p>', NULL, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (61, 'ADMISSION_NOTIFICATION_DECLARATION_DOSSIER_INCOMPLET', '<p>Notification lorsque qu''un avis émis est ''Dossier incomplet'' lors du circuit de signatures du dossier d''admission</p>', 'mail', 'Le dossier d''admission de VAR[Individu#Denomination] est incomplet', '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>Le <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> a été déclaré comme <strong>incomplet</strong> par VAR[AdmissionAvis#Auteur], le VAR[AdmissionAvis#Date].</p>
<p>Cela a pour conséquence de supprimer l''intégralité des validations préalablement effectuées.</p>
<p>Veuillez prendre connaissance de cette déclaration, en vous connectant sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission] </p>', NULL, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (62, 'ADMISSION_CONVENTION_FORMATION_DOCTORALE', '<p>Convention de formation doctorale appartenant à un dossier d''admission</p>', 'pdf', 'Convention de formation doctorale de VAR[Individu#Denomination]', '<h1 style="text-align: center;">Convention de formation doctorale</h1>
<h1 style="text-align: center;">de VAR[Individu#Denomination]</h1>
<p> </p>
<p><strong>Entre</strong> VAR[AdmissionEtudiant#DenominationEtudiant]<br /><strong>né le</strong> VAR[AdmissionEtudiant#DateNaissance]<br /><strong>à</strong> VAR[AdmissionEtudiant#VilleNaissance], VAR[AdmissionEtudiant#PaysNaissance] (VAR[AdmissionEtudiant#Nationalite])<br /><strong>ayant pour adresse</strong> VAR[AdmissionEtudiant#Adresse] VAR[AdmissionEtudiant#CodePostal], VAR[AdmissionEtudiant#VilleEtudiant]<br /><strong>Mail</strong> : VAR[AdmissionEtudiant#MailEtudiant]</p>
<p><strong>ci-après dénommé le Doctorant</strong></p>
<p><strong>Et</strong> VAR[AdmissionInscription#DenominationDirecteurThese], directeur(-trice) de thèse<br /><strong>Fonction</strong> : VAR[AdmissionInscription#FonctionDirecteurThese]<br /><strong>Unité de recherche </strong>: VAR[AdmissionInscription#UniteRecherche]<br /><strong>Établissement de rattachement</strong> : VAR[AdmissionInscription#EtablissementInscription]<br /><strong>Mail</strong> : VAR[AdmissionInscription#MailDirecteurThese]</p>
<p>VAR[AdmissionConventionFormationDoctorale#InfosCoDirecteur]</p>
<p>- Vu l’article L612-7 du Code de l’éducation, Vu les articles L412-1 et L412-2 du Code de la recherche,</p>
<p>- Vu l’arrêté du 25 mai 2016 fixant le cadre national de la formation et les modalités conduisant à la délivrance du diplôme national de doctorat, modifié par l’arrêté du 26 août 2022,</p>
<p>- Vu la charte du doctorat dans le cadre de la délivrance conjointe du doctorat entre la ComUE Normandie Université et les établissements d’inscription co-accrédités en date du 1er septembre 2023, </p>
<p>VAR[AdmissionConventionFormationDoctorale#InfosCoTutelle]</p>
<p>VAR[AdmissionConventionFormationDoctorale#InfosConventionCollaboration]</p>
<p>Il est établi la convention de formation doctorale suivante. Cette convention peut être modifiée par avenant autant de fois que nécessaire pendant le doctorat.</p>
<h3><strong>Article 1 - Objet de la convention de formation doctorale</strong></h3>
<p>La présente convention de formation, signée à la première inscription par le Doctorant, par le (ou les) (co)Directeur(s) de Thèse et, le cas échéant, par le(s) co-encadrant(s) de thèse, fixe les conditions de suivi et d''encadrement de la thèse, sous la responsabilité de l’VAR[AdmissionInscription#EtablissementInscription] (établissement d’inscription), établissement de préparation du doctorat. En cas de besoin, la convention de formation doctorale est révisable annuellement par un avenant à la convention initiale.</p>
<p>Les règles générales en matière de signature des travaux issus de la thèse, de confidentialité et de propriété intellectuelle sont précisées dans la Charte du doctorat adoptée par Normandie Université et l’VAR[AdmissionInscription#EtablissementInscription] (établissement d’inscription), également signée à la première inscription, par le Doctorant et le (ou les) Directeur(s) de Thèse.</p>
<p><strong>Titre de la thèse </strong></p>
<p>VAR[AdmissionInscription#TitreThese]</p>
<p><strong>Spécialité du diplôme</strong></p>
<p>VAR[AdmissionInscription#SpecialiteDoctorat]</p>
<p><strong>Financement</strong></p>
<p>VAR[AdmissionFinancement#ContratDoctoral]</p>
<p><strong>Établissement d''inscription du doctorant</strong></p>
<p>VAR[AdmissionInscription#EtablissementInscription]</p>
<p><strong>École doctorale</strong></p>
<p>VAR[AdmissionInscription#EcoleDoctorale]</p>
<p><strong>Temps de travail du Doctorat mené à</strong> : VAR[AdmissionFinancement#TempsTravail]</p>
<p><strong>Statut professionnel </strong>: VAR[AdmissionFinancement#StatutProfessionnel]</p>
<h3><strong>Article 2 – Laboratoire d’accueil</strong></h3>
<p>Le doctorant réalise sa thèse au sein de :</p>
<ul>
<li style="font-weight: 400; text-align: start;">Unité d''accueil (UMR, U INSERM, UR, Laboratoire privé...) : VAR[AdmissionInscription#UniteRecherche]</li>
<li style="font-weight: 400; text-align: start;">Directeur de l''unité (Nom, Prénom, coordonnées téléphoniques et courriel) : VAR[AdmissionConventionFormationDoctorale#ResponsablesURDirecteurThese]</li>
</ul>
<p>Et (en cas de co-tutelle ou co-direction de thèse) :</p>
<ul>
<li style="font-weight: 400; text-align: start;">Unité d''accueil (EA, UMR, U INSERM, UR, Laboratoire privé …) : VAR[AdmissionInscription#UniteRechercheCoDirection]</li>
<li style="font-weight: 400; text-align: start;">Directeur de l''unité (Nom, Prénom, coordonnées téléphoniques et courriel) : VAR[AdmissionConventionFormationDoctorale#ResponsablesURCoDirecteurThese]</li>
</ul>
<h3><strong>Article 3 - Méthodes et Moyens</strong></h3>
<p><strong>3-1 Calendrier prévisionnel du projet de recherche</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#CalendrierPrevisionnel]</p>
<p><strong>3-2 Modalités d''encadrement, de suivi de la formation et d''avancement des recherches du doctorant</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ModalitesEncadrement]</p>
<p><strong>3-3 Conditions matérielles de réalisation du projet de recherche et conditions de sécurité spécifiques si nécessaire</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ConditionsProjRech]</p>
<p><strong>3-4 Modalités d''intégration dans l''unité ou l’équipe de recherche</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ModalitesIntegrationUR]</p>
<p><strong>3-5 Partenariats impliqués par le projet de thèse</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#PartenariatsProjThese]</p>
<h3><strong>Article 4 - Confidentialité des travaux de recherche</strong></h3>
<p>Caractère confidentiel des travaux :</p>
<p>VAR[AdmissionInscription#ConfidentialiteSouhaitee]</p>
<p>Si oui, motivation de la demande de confidentialité par le doctorant et la direction de thèse:</p>
<p>VAR[AdmissionConventionFormationDoctorale#MotivationDemandeConfidentialite]</p>
<h3><strong>Article 5 - Projet professionnel du doctorant</strong></h3>
<p>VAR[AdmissionConventionFormationDoctorale#ProjetProDoctorant]</p>
<p> </p>
<h2>Validations accordées à la convention de formation doctorale</h2>
<p>VAR[AdmissionConventionFormationDoctorale#Operations]</p>', 'table { border-collapse: collapse;  width: 100%;  } th, td { border: 1px solid #000; padding: 8px;  }body{font-size: 9pt;}.pas_valeur_avis_renseigne { background-color: #dddddd;} p{padding:unset}', 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (63, 'ADMISSION_RECAPITULATIF', NULL, 'pdf', 'Récapitulatif du dossier d''admission de VAR[Individu#Denomination]', '<h1 style="text-align: center;">Récapitulatif du dossier d''admission</h1>
<h1 style="text-align: center;">de VAR[Individu#Denomination]</h1>
<h2>Étudiant</h2>
<h3>Informations concernant l''étudiant</h3>
<table style="height: 125px; width: 929px;">
<tbody>
<tr>
<td style="width: 163.5px;"><strong>Numéro I.N.E :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#INE]</td>
<td style="width: 145px;"><strong> </strong></td>
<td style="width: 303px;"> </td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Étudiant :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#DenominationEtudiant]</td>
<td style="width: 145px;"><strong>Date de naissance :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#DateNaissance]</td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Ville de naissance :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#VilleNaissance]</td>
<td style="width: 145px;"><strong>Pays de naissance :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#PaysNaissance]</td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Nationalité :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#Nationalite]</td>
<td style="width: 145px;"><strong>Adresse :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#Adresse]</td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Code postal :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#CodePostal]</td>
<td style="width: 145px;"><strong>Ville :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#VilleEtudiant]</td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Numéro de téléphone :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#NumeroTelephone]</td>
<td style="width: 145px;"><strong>Mail :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#MailEtudiant]</td>
</tr>
</tbody>
</table>
<p>Etes-vous en situation de handicap ? VAR[AdmissionEtudiant#SituationHandicap]</p>
<h3>Niveau permettant l''accès au doctorat</h3>
<p>VAR[AdmissionEtudiant#NiveauEtude] </p>
<p>VAR[AdmissionRecapitulatif#InfosDiplome]</p>
<h2>Inscription </h2>
<h3>Informations concernant son inscription demandée</h3>
<ul>
<li><strong>Spécialité d''inscription :</strong> VAR[AdmissionInscription#SpecialiteDoctorat]</li>
<li><strong>Composante de rattachement : </strong>VAR[AdmissionInscription#ComposanteRattachement]</li>
<li><strong>École Doctorale :</strong> VAR[AdmissionInscription#EcoleDoctorale]</li>
<li><strong>Unité de recherche :</strong> VAR[AdmissionInscription#UniteRecherche]</li>
<li><strong>Établissement d''inscription</strong> : VAR[AdmissionInscription#EtablissementInscription] </li>
<li><strong>Directeur(-trice) de thèse :</strong> VAR[AdmissionInscription#DenominationDirecteurThese]
<ul>
<li><strong>Fonction du directeur(-trice) de thèse :</strong> VAR[AdmissionInscription#FonctionDirecteurThese]</li>
</ul>
</li>
<li><strong>Titre provisoire de la thèse :</strong> VAR[AdmissionInscription#TitreThese]</li>
</ul>
<h3>Spéciﬁcités envisagées concernant son inscription</h3>
<ul>
<li><strong>Conﬁdentialité souhaitée :</strong> VAR[AdmissionInscription#ConfidentialiteSouhaitee]</li>
<li><strong>Cotutelle envisagée :</strong> VAR[AdmissionInscription#CotutelleEnvisagee]</li>
<li><strong>Codirection demandée :</strong> VAR[AdmissionInscription#CoDirectionDemandee]
<ul>
<li><strong>Si oui, fonction du codirecteur(-trice) de thèse :</strong> VAR[AdmissionInscription#FonctionCoDirecteurThese]</li>
</ul>
</li>
<li><strong>Co-encadrement envisagé :</strong> VAR[AdmissionInscription#CoEncadrementEnvisage]</li>
</ul>
<h2>Financement</h2>
<p><strong>Avez-vous un contrat doctoral</strong> <strong>?</strong> VAR[AdmissionFinancement#ContratDoctoral]</p>
<p><strong>Si oui, détails du contrat doctoral</strong> : VAR[AdmissionFinancement#DetailContratDoctoral]</p>
<p><strong>Temps de travail du Doctorat mené à</strong> : VAR[AdmissionFinancement#TempsTravail]</p>
<p><strong>Êtes-vous salarié ?</strong> VAR[AdmissionFinancement#EstSalarie]</p>
<ul>
<li><strong>Si oui, Statut professionnel </strong>: VAR[AdmissionFinancement#StatutProfessionnel]</li>
<li><strong>Si oui, Établissement hébergeant le laboratoire de recherche</strong> :  VAR[AdmissionFinancement#EtablissementLaboratoireRecherche]</li>
</ul>
<p> </p>
<h2>Validations et Avis accordés au dossier d''admission</h2>
<p>VAR[AdmissionRecapitulatif#Operations]</p>
<h2>Validation par la direction de l''établissement</h2>
<ul>
<li>Favorable</li>
<li>Défavorable
<ul>
<li>Motif du refus :</li>
</ul>
</li>
</ul>
<p> </p>
<p> </p>
<p>Fait à ____________________, le ________________,</p>
<p>Signature de VAR[String#ToString]</p>', 'table { border-collapse: collapse;  width: 100%;  } th, td { border: 1px solid #000; padding: 8px;  }body{font-size: 9pt;}.pas_valeur_avis_renseigne { background-color: #dddddd;}', 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (64, 'ADMISSION_COMMENTAIRES_AJOUTES', '<p>Notification lorsque le gestionnaire a ajouté des commentaires au dossier d''admission</p>', 'mail', 'Commentaires ajoutés à votre dossier d''admission', '<p>Bonjour, </p>
<p style="font-family: ''Segoe UI'', ''Lucida Sans'', sans-serif; font-size: 14.16px; background-color: #fdfcfa;">Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p><strong>Des commentaires ont été ajoutés</strong> par vos gestionnaires à votre dossier d''admission. </p>
<p><span style="background-color: #fdfcfa; font-family: ''Segoe UI'', ''Lucida Sans'', sans-serif; font-size: 14.16px;">Merci de prendre connaissance des commentaires en vous connectant sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission]</span><span style="background-color: #fdfcfa; font-family: ''Segoe UI'', ''Lucida Sans'', sans-serif; font-size: 14.16px;"> </span></p>', NULL, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (65, 'ADMISSION_NOTIFICATION_DOSSIER_COMPLET', '<p>Mail pour notifier l''étudiant que son dossier est complet</p>', 'mail', 'Votre dossier d''admission est complet', '<p>Bonjour, </p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>Votre dossier d''admission est noté comme <strong>complet</strong> par votre gestionnaire.</p>
<p>Vous pouvez dès à présent faire votre attestation sur l''honneur en vous connectant sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission] </p>', NULL, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (66, 'ADMISSION_NOTIFICATION_DOSSIER_INCOMPLET', '<p>Mail pour notifier l''étudiant que son dossier est incomplet</p>', 'mail', 'Votre dossier d''admission est incomplet', '<p>Bonjour, </p>
           <p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>Votre dossier d''admission est noté comme <strong>incomplet</strong> par votre gestionnaire.</p>
           <p>Veuillez prendre connaissance des commentaires ajoutés à votre dossier, en vous connectant sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission] </p>', NULL, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (67, 'ADMISSION_AVIS_AJOUTE', '<p>Mail pour notifier qu''un avis a été ajouté au dossier d''admission</p>', 'mail', 'Dossier d''admission de VAR[Individu#Denomination] ', '<p>Bonjour,</p>
           <p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>Un avis a été ajouté au <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> par VAR[AdmissionAvis#Auteur], le VAR[AdmissionAvis#Date]</p>', NULL, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (68, 'ADMISSION_AVIS_MODIFIE', '<p>Mail pour notifier qu''un avis a été modifié du dossier d''admission</p>', 'mail', 'Dossier d''admission de VAR[Individu#Denomination]', '<p>Bonjour,</p>
           <p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>L''<strong>VAR[TypeValidation#Libelle]</strong> du <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> datant du VAR[AdmissionAvis#Date] a été modifié<strong> </strong>VAR[AdmissionAvis#Modificateur]</p>', NULL, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (69, 'ADMISSION_AVIS_SUPPRIME', '<p>Mail pour notifier qu''un avis a été supprimé du dossier d''admission</p>', 'mail', 'Dossier d''admission de VAR[Individu#Denomination]', '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>L''<strong>VAR[TypeValidation#Libelle]</strong> du <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> datant du VAR[AdmissionAvis#Date] a été <strong>supprimé </strong>VAR[AdmissionAvis#Destructeur]</p>', NULL, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (70, 'ADMISSION_DERNIERE_VALIDATION_AJOUTEE', '<p>Mail pour notifier que la dernière validation a été ajoutée au dossier d''admission</p>', 'mail', 'Dossier d''admission de VAR[Individu#Denomination] ', '<p>Bonjour,</p>
           <p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>Le <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> a été <strong>validé</strong> par VAR[AdmissionAvis#Auteur], le VAR[AdmissionAvis#Date]</p>
<p>Le circuit de signature de votre dossier est maintenant terminé. </p>', NULL, 'Admission\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (4, 'FORMATION_INSCRIPTION_ECHEC', '<p>Courrier envoyé lors qu''un doctorant est non classé</p>', 'mail', 'Information sur la formation VAR[Formation#Libelle]', '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>Vous étiez inscrit sur liste complémentaire à la formation VAR[Formation#Libelle] se déroulant :<br />VAR[Session#SeancesTable]</p>
<p><strong>Cependant, aucune place ne s’étant libérée, nous sommes au regret de vous informer que vous ne pourrez pas participer à la formation.</strong></p>
<p><br />Cordialement,</p>
<p>VAR[Session#Responsable]</p>', NULL, 'Formation\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (71, 'PROPOSITION_SUPPRESSION', '<p>Suppression des informations concernant la proposition de soutenance</p>', 'mail', 'Votre proposition de soutenance a été supprimée', '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP SyGAL</p>
<p>Votre proposition de soutenance vient d''être <strong>supprimée.</strong></p>
<p>Veuillez vous rapprocher de la/le gestionnaire de votre école doctorale afin d''avoir plus d''informations à ce sujet.</p>', NULL, 'Soutenance\Provider\Template');
INSERT INTO public.unicaen_renderer_template (id, code, description, document_type, document_sujet, document_corps, document_css, namespace) VALUES (10, 'FORMATION_INSCRIPTION_CLOSE', '<p>Courrier envoyé à la clôture des inscriptions pour les étudiants non classés</p>', 'mail', 'Les inscriptions à la formation VAR[Formation#Libelle] sont maintenant closes.', '<p>Bonjour,</p>
<p>Les inscriptions à la formation VAR[Formation#Libelle] sont maintenant closes.<br />Si à j-5 vous ne recevez pas un mail vous indiquant que votre demande est prise est compte, nous sommes au regret de vous informer que nous ne pourrons donner suite à votre demande.<br /><br /></p>
<p>En vous souhaitant une bonne journée,<br />VAR[Formation#Responsable]</p>', NULL, 'Formation\Provider\Template');


--
-- Data for Name: version_fichier; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.version_fichier (id, code, libelle) VALUES (1, 'VA', 'Version d''archivage');
INSERT INTO public.version_fichier (id, code, libelle) VALUES (2, 'VD', 'Version de diffusion');
INSERT INTO public.version_fichier (id, code, libelle) VALUES (3, 'VO', 'Version originale');
INSERT INTO public.version_fichier (id, code, libelle) VALUES (4, 'VAC', 'Version d''archivage corrigée');
INSERT INTO public.version_fichier (id, code, libelle) VALUES (5, 'VDC', 'Version de diffusion corrigée');
INSERT INTO public.version_fichier (id, code, libelle) VALUES (6, 'VOC', 'Version originale corrigée');


--
-- Data for Name: wf_etape; Type: TABLE DATA; Schema: public; Owner: :dbuser
--

INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (1, 'DEPOT_VERSION_ORIGINALE', 10, 1, true, 'these/depot', 'Téléversement de la thèse', 'Téléversement de la thèse', 'Téléversement de la thèse non effectué', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (2, 'AUTORISATION_DIFFUSION_THESE', 13, 1, true, 'these/depot', 'Autorisation de diffusion de la thèse', 'Autorisation de diffusion de la thèse', 'Autorisation de diffusion non remplie', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (3, 'SIGNALEMENT_THESE', 30, 1, true, 'these/description', 'Signalement de la thèse', 'Signalement de la thèse', 'Signalement non renseigné', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (4, 'ARCHIVABILITE_VERSION_ORIGINALE', 40, 1, true, 'these/archivage', 'Archivabilité de la thèse', 'Archivabilité de la thèse', 'Archivabilité non testée', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (5, 'DEPOT_VERSION_ARCHIVAGE', 50, 2, true, 'these/archivage', 'Téléversement d''une version retraitée de la thèse', 'Téléversement d''une version retraitée de la thèse', 'Téléversement d''une version retraitée non effectué', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (6, 'ARCHIVABILITE_VERSION_ARCHIVAGE', 60, 2, true, 'these/archivage', 'Archivabilité de la version retraitée de la thèse', 'Archivabilité de la version retraitée de la thèse', 'Archivabilité de la version retraitée non testée', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (7, 'VERIFICATION_VERSION_ARCHIVAGE', 70, 2, true, 'these/archivage', 'Vérification de la version retraitée de la thèse', 'Vérification de la version retraitée de la thèse', 'Vérification de la version retraitée non effectuée', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (8, 'RDV_BU_SAISIE_DOCTORANT', 80, 1, true, 'these/rdv-bu', 'Saisie des coordonnées et disponibilités du doctorant pour le rendez-vous à la bibliothèque universitaire', 'Saisie des coordonnées et disponibilités du doctorant pour le rendez-vous à la bibliothèque universitaire', 'Saisie des coordonnées et disponibilités pour le rendez-vous à la BU non effectuée', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (9, 'RDV_BU_SAISIE_BU', 90, 1, true, 'these/rdv-bu', 'Saisie des informations pour la bibliothèque universitaire', 'Saisie des informations pour la bibliothèque universitaire', 'Saisie infos BU non effectuée', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (10, 'RDV_BU_VALIDATION_BU', 100, 1, true, 'these/rdv-bu', 'Validation', 'Validation', 'Validation de la BU non effectuée', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (11, 'ATTESTATIONS', 18, 1, true, 'these/depot', 'Attestations', 'Attestations', 'Attestations non renseignées', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (12, 'VALIDATION_PAGE_DE_COUVERTURE', 8, 1, true, 'these/validation-page-de-couverture', 'Validation de la page de couverture', 'Validation de la page de couverture', 'Validation de la page de couverture non effectuée', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (31, 'DEPOT_VERSION_ORIGINALE_CORRIGEE', 200, 1, true, 'these/depot-version-corrigee', 'Téléversement de la thèse corrigée', 'Téléversement de la thèse corrigée', 'Téléversement de la thèse corrigée non effectué', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (32, 'ARCHIVABILITE_VERSION_ORIGINALE_CORRIGEE', 240, 1, true, 'these/archivage-version-corrigee', 'Archivabilité de la version originale de la thèse corrigée', 'Archivabilité de la version originale de la thèse corrigée', 'Archivabilité de la version originale de la thèse corrigée non testée', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (33, 'DEPOT_VERSION_ARCHIVAGE_CORRIGEE', 250, 2, true, 'these/archivage-version-corrigee', 'Téléversement d''une version retraitée de la thèse corrigée', 'Téléversement d''une version retraitée de la thèse corrigée', 'Téléversement d''une version retraitée de la thèse corrigée non effectué', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (34, 'ARCHIVABILITE_VERSION_ARCHIVAGE_CORRIGEE', 260, 2, true, 'these/archivage-version-corrigee', 'Archivabilité de la version retraitée de la thèse corrigée', 'Archivabilité de la version retraitée de la thèse corrigée', 'Archivabilité de la version retraitée de la thèse corrigée non testée', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (35, 'VERIFICATION_VERSION_ARCHIVAGE_CORRIGEE', 270, 2, true, 'these/archivage-version-corrigee', 'Vérification de la version retraitée de la thèse corrigée', 'Vérification de la version retraitée de la thèse corrigée', 'Vérification de la version retraitée de la thèse corrigée non effectuée', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (38, 'DEPOT_VERSION_CORRIGEE_VALIDATION_DOCTORANT', 280, 1, true, 'these/validation-these-corrigee', 'Validation automatique du dépôt de votre thèse corrigée', 'Validation automatique du dépôt de la thèse corrigée', 'Validation automatique du dépôt de la thèse corrigée non effectuée', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (39, 'DEPOT_VERSION_CORRIGEE_VALIDATION_DIRECTEUR', 290, 1, true, 'these/validation-these-corrigee', 'Validation de la thèse corrigée par le président du jury', 'Validation de la thèse corrigée par le président du jury', 'Validation de la thèse corrigée par le président du jury non effectuée', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (51, 'ATTESTATIONS_VERSION_CORRIGEE', 210, 1, true, 'these/depot-version-corrigee', 'Attestations version corrigée', 'Attestations version corrigée', 'Attestations version corrigée non renseignées', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (52, 'AUTORISATION_DIFFUSION_THESE_VERSION_CORRIGEE', 220, 1, true, 'these/depot-version-corrigee', 'Autorisation de diffusion de la version corrigée', 'Autorisation de diffusion de la version corrigée', 'Autorisation de diffusion de la version corrigée non remplie', NULL);
INSERT INTO public.wf_etape (id, code, ordre, chemin, obligatoire, route, libelle_acteur, libelle_autres, desc_non_franchie, desc_sans_objectif) VALUES (60, 'REMISE_EXEMPLAIRE_PAPIER_THESE_CORRIGEE', 300, 1, true, 'these/version-papier', 'Remise de l''exemplaire papier de la thèse corrigée', 'Remise de l''exemplaire papier de la thèse corrigée', 'Remise de l''exemplaire papier de la thèse corrigée', NULL);


--
-- Name: admission_type_validation_id_seq; Type: SEQUENCE SET; Schema: public; Owner: :dbuser
--

SELECT pg_catalog.setval('public.admission_type_validation_id_seq', 8, true);


--
-- Name: categorie_privilege_id_seq; Type: SEQUENCE SET; Schema: public; Owner: :dbuser
--

SELECT pg_catalog.setval('public.categorie_privilege_id_seq', 5179, true);


--
-- Name: discipline_sise_id_seq; Type: SEQUENCE SET; Schema: public; Owner: :dbuser
--

SELECT pg_catalog.setval('public.discipline_sise_id_seq', 47, true);


--
-- Name: formation_enquete_categorie_id_seq; Type: SEQUENCE SET; Schema: public; Owner: :dbuser
--

SELECT pg_catalog.setval('public.formation_enquete_categorie_id_seq', 6, true);


--
-- Name: formation_enquete_question_id_seq; Type: SEQUENCE SET; Schema: public; Owner: :dbuser
--

SELECT pg_catalog.setval('public.formation_enquete_question_id_seq', 15, true);


--
-- Name: privilege_id_seq; Type: SEQUENCE SET; Schema: public; Owner: :dbuser
--

SELECT pg_catalog.setval('public.privilege_id_seq', 1259, true);


--
-- Name: unicaen_avis_type_valeur_complem_ordre_seq; Type: SEQUENCE SET; Schema: public; Owner: :dbuser
--

SELECT pg_catalog.setval('public.unicaen_avis_type_valeur_complem_ordre_seq', 50, true);


--
-- Name: unicaen_avis_valeur_ordre_seq; Type: SEQUENCE SET; Schema: public; Owner: :dbuser
--

SELECT pg_catalog.setval('public.unicaen_avis_valeur_ordre_seq', 9, true);


--
-- Name: unicaen_parametre_categorie_id_seq; Type: SEQUENCE SET; Schema: public; Owner: :dbuser
--

SELECT pg_catalog.setval('public.unicaen_parametre_categorie_id_seq', 4, true);


--
-- Name: unicaen_parametre_parametre_id_seq; Type: SEQUENCE SET; Schema: public; Owner: :dbuser
--

SELECT pg_catalog.setval('public.unicaen_parametre_parametre_id_seq', 19, true);


--
-- Name: unicaen_renderer_macro_id_seq; Type: SEQUENCE SET; Schema: public; Owner: :dbuser
--

SELECT pg_catalog.setval('public.unicaen_renderer_macro_id_seq', 125, true);


--
-- Name: unicaen_renderer_template_id_seq; Type: SEQUENCE SET; Schema: public; Owner: :dbuser
--

SELECT pg_catalog.setval('public.unicaen_renderer_template_id_seq', 71, true);


--
-- PostgreSQL database dump complete
--

