-- RESPONSABLES --------------------------------------------------------------------------------------------------------

-- Module 1
-- -- Responsable : Patrice Lerouge (863448)
-- -- Site : URN 3
-- -- Specifique : NBISE 12401

-- Module 2
-- -- Responsable : Sandrine Maviel (5063)
-- -- Site : UCN 2

INSERT INTO formation_module (id, libelle, description, lien, site_id, responsable_id, modalite, type, type_structure_id, taille_liste_principale, taille_liste_complementaire, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction) VALUES (1, 'Sécurité à la paillasse', '<p>Module pr&eacute;sentant les r&egrave;gles de s&eacute;curit&eacute; dans un laboratoire</p>', null, 3, 863448, null, 'S', 12401, null, null, 1446, '2021-06-29 16:28:37.000000', 1446, '2021-07-23 12:26:37.000000', null, null);
INSERT INTO formation_module (id, libelle, description, lien, site_id, responsable_id, modalite, type, type_structure_id, taille_liste_principale, taille_liste_complementaire, histo_createur_id, histo_creation, histo_modificateur_id, histo_modification, histo_destructeur_id, histo_destruction) VALUES (2, 'Créer une formation', null, null, 2, 5063, 'P', 'S', null, 10, null, 1446, '2021-06-25 17:12:09.000000', 1446, '2021-06-25 17:12:09.000000', null, null);
alter sequence formation_module_id_seq restart with 3;

-- SESSIONS ------------------------------------------------------------------------------------------------------------

-- -- Responsable : Patrice Lerouge (863448)
-- -- Responsable : Sandrine Maviel (5063)
-- -- Site : UCN 2
-- -- Site : URN 3
-- -- Site : INSA 5
-- -- Specifique : NBISE 12401

INSERT INTO formation_session (id, module_id, description, taille_liste_principale, taille_liste_complementaire, type_structure_id, site_id, responsable_id, modalite, type, etat_code, session_index, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (3, 1, null, 30, 100, null, 5, null, 'D', 'T', 'P', 4, '2021-06-29 18:18:08.000000', 1446, '2021-06-29 18:18:08.000000', 1446, null, null);
INSERT INTO formation_session (id, module_id, description, taille_liste_principale, taille_liste_complementaire, type_structure_id, site_id, responsable_id, modalite, type, etat_code, session_index, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (4, 1, null, 1, 10, null, 2, null, 'P', 'S', 'F', 1, '2021-06-29 16:44:49.000000', 1446, '2021-06-30 09:24:54.000000', 1446, null, null);
INSERT INTO formation_session (id, module_id, description, taille_liste_principale, taille_liste_complementaire, type_structure_id, site_id, responsable_id, modalite, type, etat_code, session_index, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (5, 2, null, 2, 1, 12401, 2, null, 'D', 'S', 'F', 1, '2021-06-25 18:00:33.000000', 1446, '2021-07-05 15:37:20.000000', 1446, null, null);
INSERT INTO formation_session (id, module_id, description, taille_liste_principale, taille_liste_complementaire, type_structure_id, site_id, responsable_id, modalite, type, etat_code, session_index, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (8, 2, null, null, null, 12401, 3, 863448, 'P', 'T', 'O', 3, '2021-06-29 09:35:30.000000', 1446, '2021-07-01 15:27:09.000000', 1446, null, null);
INSERT INTO formation_session (id, module_id, description, taille_liste_principale, taille_liste_complementaire, type_structure_id, site_id, responsable_id, modalite, type, etat_code, session_index, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (7, 2, null, 10, 5, 12401, 2, 863448, 'D', 'S', 'O', 5, '2021-07-01 15:32:32.000000', 1446, '2021-07-02 14:33:46.000000', 1446, null, null);
INSERT INTO formation_session (id, module_id, description, taille_liste_principale, taille_liste_complementaire, type_structure_id, site_id, responsable_id, modalite, type, etat_code, session_index, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (2, 2, null, null, null, null, 3, 863448, 'P', 'T', 'O', 4, '2021-06-29 14:12:00.000000', 1446, '2021-06-29 14:12:00.000000', 1446, null, null);
INSERT INTO formation_session (id, module_id, description, taille_liste_principale, taille_liste_complementaire, type_structure_id, site_id, responsable_id, modalite, type, etat_code, session_index, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (6, 1, null, 5, 5, null, 5, 5063, 'D', 'T', 'P', 3, '2021-06-29 17:52:48.000000', 1446, '2021-07-05 14:58:34.000000', 1446, null, null);
INSERT INTO formation_session (id, module_id, description, taille_liste_principale, taille_liste_complementaire, type_structure_id, site_id, responsable_id, modalite, type, etat_code, session_index, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (9, 1, null, null, null, null, null, 5063, null, null, null, 2, '2021-06-29 17:18:39.000000', 1446, '2021-06-29 17:18:39.000000', 1446, null, null);
alter sequence formation_session_id_seq restart with 10;

-- SEANCES -------------------------------------------------------------------------------------------------------------
INSERT INTO formation_seance (id, session_id, debut, fin, lieu, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (2, 4, '2021-07-03 09:00:00.000000', '2021-07-03 12:00:00.000000', 'SE-212', null, '2021-07-01 16:14:16.000000', 1446, '2021-07-01 16:14:16.000000', 1446, null, null);
INSERT INTO formation_seance (id, session_id, debut, fin, lieu, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (3, 3, '2021-08-01 09:00:00.000000', '2021-08-01 10:00:00.000000', 'Visio BBB', null, '2021-07-02 17:42:49.000000', 1446, '2021-07-02 17:42:49.000000', 1446, null, null);
INSERT INTO formation_seance (id, session_id, debut, fin, lieu, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (4, 4, '2021-07-02 09:00:00.000000', '2021-07-02 13:15:00.000000', 'Amphi Daure', '<p>Pr&eacute;sentation des concept de base en amphith&eacute;atre</p>', '2021-06-28 15:29:39.000000', 1446, '2021-07-01 16:20:44.000000', 1446, null, null);
INSERT INTO formation_seance (id, session_id, debut, fin, lieu, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (5, 2, '2021-06-25 09:00:00.000000', '2021-06-25 12:00:00.000000', 'SE-212', '<p>Partie th&eacute;orique avec guide de formation information</p>', '2021-07-02 15:22:42.000000', 1446, '2021-07-02 15:22:42.000000', 1446, null, null);
INSERT INTO formation_seance (id, session_id, debut, fin, lieu, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (6, 2, '2021-06-25 13:30:00.000000', '2021-06-25 17:00:00.000000', 'Labo de bio', '<p>Mise en pratique &agrave; la paillasse</p>', '2021-07-02 15:23:22.000000', 1446, '2021-07-02 15:23:22.000000', 1446, null, null);
alter sequence formation_seance_id_seq restart with 7;

-- INSCRIPTIONS --------------------------------------------------------------------------------------------------------

-- -- Doctorant : CAHAREL Stephanie (32476)
-- -- Doctorant : GUENERON Josselin  (38108)
-- -- Doctorant : DENOYELLE Gaelle (30513)
-- -- Doctorant : FRESNEAU Nathalie (33130)

INSERT INTO formation_inscription (id, session_id, doctorant_id, liste, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (1, 2, 32476, 'P', null, '2021-07-02 17:42:16.000000', 1446, '2021-07-02 17:43:01.000000', 1446, null, null);
INSERT INTO formation_inscription (id, session_id, doctorant_id, liste, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (3, 2, 38108, 'P', null, '2021-07-02 17:43:25.000000', 1446, '2021-07-23 09:09:18.000000', 1446, null, null);
INSERT INTO formation_inscription (id, session_id, doctorant_id, liste, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (4, 4, 30513, 'P', null, '2021-07-02 08:41:19.000000', 1446, '2021-07-02 09:48:27.000000', 1446, '2021-07-02 09:48:27.000000', 1446);
INSERT INTO formation_inscription (id, session_id, doctorant_id, liste, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (5, 4, 38108, 'P', null, '2021-07-01 16:24:49.000000', 1446, '2021-07-02 09:49:13.000000', 1446, null, null);
INSERT INTO formation_inscription (id, session_id, doctorant_id, liste, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (6, 4, 33130, 'P', null, '2021-07-01 16:25:15.000000', 1446, '2021-07-02 09:49:13.000000', 1446, null, null);
INSERT INTO formation_inscription (id, session_id, doctorant_id, liste, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (7, 4, 33130, 'C', null, '2021-07-01 16:26:51.000000', 1446, '2021-07-02 09:49:23.000000', 1446, '2021-07-02 09:49:23.000000', 1446);
INSERT INTO formation_inscription (id, session_id, doctorant_id, liste, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (8, 3, 38108, null, null, '2021-06-30 09:23:45.000000', 1446, '2021-07-02 09:37:10.000000', 1446, null, null);
INSERT INTO formation_inscription (id, session_id, doctorant_id, liste, description, histo_creation, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id) VALUES (10, 3, 32476, 'P', null, '2021-06-30 09:24:40.000000', 1446, '2021-06-30 10:03:33.000000', 1446, null, null);
alter sequence formation_inscription_id_seq restart with 11;

-- PRESENCES -----------------------------------------------------------------------------------------------------------
INSERT INTO formation_presence (id, inscription_id, seance_id, temoin, description, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (1, 5, 4, 'O', null, 1446, '2021-07-23 14:53:47.000000', 1446, null, null, '2021-07-23 14:53:55.000000');
INSERT INTO formation_presence (id, inscription_id, seance_id, temoin, description, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (2, 5, 2, 'O', null, 1446, '2021-07-23 14:56:14.000000', 1446, null, null, '2021-07-23 14:55:48.000000');
INSERT INTO formation_presence (id, inscription_id, seance_id, temoin, description, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (3, 6, 2, 'O', null, 1446, '2021-07-23 14:56:15.000000', 1446, null, null, '2021-07-23 14:56:19.000000');
alter sequence formation_presence_id_seq restart with 4;

-- ENQUETE - QUESTIONS -------------------------------------------------------------------------------------------------
INSERT INTO formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (1, 'La formation a-t''elle été bien animée ?', null, 2, 1, '2021-07-08 00:00:00.000000', 1446, null, null, '2021-07-06 00:00:00.000000');
INSERT INTO formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (2, 'La formation vous a-t''elle satisfaite ?', 'Question sur la satisfaction globale de la formation suivie', 1, 1, null, null, null, null, '2021-07-05 00:00:00.000000');
INSERT INTO formation_enquete_question (id, libelle, description, ordre, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (3, 'Et alors ?', null, 3, 1446, '2021-07-08 00:00:00.000000', 1446, null, null, '2021-07-08 00:00:00.000000');
alter sequence formation_enquete_question_id_seq restart with 4;

-- ENQUETE - REPONSES --------------------------------------------------------------------------------------------------
INSERT INTO formation_enquete_reponse (id, inscription_id, question_id, niveau, description, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (2, 1, 1, 4, 'Chouette', 1, null, null, null, null, '2021-07-07 00:00:00.000000');
INSERT INTO formation_enquete_reponse (id, inscription_id, question_id, niveau, description, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (3, 1, 2, 5, 'Génial !', 1446, '2021-07-08 00:00:00.000000', 1446, null, null, '2021-07-08 00:00:00.000000');
INSERT INTO formation_enquete_reponse (id, inscription_id, question_id, niveau, description, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (4, 3, 1, 5, null, 1446, '2021-07-08 00:00:00.000000', 1446, null, null, '2021-07-08 00:00:00.000000');
INSERT INTO formation_enquete_reponse (id, inscription_id, question_id, niveau, description, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (5, 3, 2, 1, null, 1446, '2021-07-08 00:00:00.000000', 1446, null, null, '2021-07-08 00:00:00.000000');
INSERT INTO formation_enquete_reponse (id, inscription_id, question_id, niveau, description, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (6, 4, 1, 4, 'Mouais', 1446, '2021-07-08 00:00:00.000000', 1446, null, null, '2021-07-08 00:00:00.000000');
INSERT INTO formation_enquete_reponse (id, inscription_id, question_id, niveau, description, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (8, 4, 3, 3, 'Aucun', 1446, '2021-07-08 00:00:00.000000', 1446, null, null, '2021-07-08 00:00:00.000000');
INSERT INTO formation_enquete_reponse (id, inscription_id, question_id, niveau, description, histo_createur_id, histo_modification, histo_modificateur_id, histo_destruction, histo_destructeur_id, histo_creation) VALUES (9, 4, 2, 2, null, 1446, '2021-07-07 00:00:00.000000', 1446, null, null, '2021-07-07 00:00:00.000000');
alter sequence formation_enquete_reponse_id_seq restart with 10;