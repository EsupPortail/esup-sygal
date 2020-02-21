INSERT INTO ROLE (ID, CODE, LIBELLE, SOURCE_CODE, SOURCE_ID, ROLE_ID, THESE_DEP, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, ORDRE_AFFICHAGE)
VALUES (1001, 'CST_E', 'Examinateur (CST)', 'SyGAL::CST_E', 1, 'Examinateur (CST)', 1, 1, 1, 'zzzz_1');
INSERT INTO ROLE (ID, CODE, LIBELLE, SOURCE_CODE, SOURCE_ID, ROLE_ID, THESE_DEP, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, ORDRE_AFFICHAGE)
VALUES (1002, 'CST_O', 'Observateur (CST)', 'SyGAL::CST_O', 1, 'Observateur (CST)', 1, 1, 1, 'zzzz_2');


INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (2000, 'CS_comite', 'Comité suivi - Gestion des comités de suivi', 2000);
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (2001, 'CS_membre', 'Comité suivi - Gestion des membres', 2100);
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (2002, 'CS_compterendu', 'Comité suivi - Gestion des comptes rendus', 2200);
INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (2003, 'CS_notification', 'Comité suivi - Gestion des notifications', 2300);

INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2000, 2000, 'ComiteSuivi_afficher', 'Afficher les comités de suivi', 100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2001, 2000, 'ComiteSuivi_ajouter', 'Ajouter les comités de suivi', 200);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2002, 2000, 'ComiteSuivi_modifier', 'Modifier les comités de suivi', 300);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2003, 2000, 'ComiteSuivi_historiser', 'Historiser les comités de suivi', 400);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2004, 2000, 'ComiteSuivi_supprimer', 'Supprimer les comités de suivi', 500);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2005, 2000, 'ComiteSuivi_valider', 'Valider les comités de suivi', 600);

INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2100, 2001, 'ComiteMembre_afficher', 'Afficher les membres d''un comité', 100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2101, 2001, 'ComiteMembre_ajouter', 'Ajouter un membre à un comité', 200);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2102, 2001, 'ComiteMembre_modifier', 'Modifier un membre d''un comité', 300);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2103, 2001, 'ComiteMembre_historiser', 'Historiser un membre d''un comité', 400);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2104, 2001, 'ComiteMembre_supprimer', 'Supprimer un membre d''un comité', 500);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2105, 2001, 'ComiteMembre_lier', 'Lier un membre d''un comité', 600);

INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2200, 2002, 'ComiteRapport_afficher', 'Afficher les rapports d''un comité', 100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2201, 2002, 'ComiteRapport_ajouter', 'Ajouter un rapport à un comité', 200);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2202, 2002, 'ComiteRapport_modifier', 'Modifier un rapport d''un comité', 300);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2203, 2002, 'ComiteRapport_historiser', 'Historiser un rapport d''un comité', 400);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (2204, 2002, 'ComiteRapport_supprimer', 'Supprimer un rapport d''un comité', 500);

INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (14, 'FINALISATION_COMITE_SUIVI', 'Finalisation du comité de suivi de thèse');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (15, 'VALIDATION_COMITE_SUIVI', 'Validation du comité de suivi de thèse');
drop index VALIDATION_UN;