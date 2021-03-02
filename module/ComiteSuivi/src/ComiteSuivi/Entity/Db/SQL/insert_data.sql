INSERT INTO ROLE (ID, CODE, LIBELLE, SOURCE_CODE, SOURCE_ID, ROLE_ID, THESE_DEP, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, ORDRE_AFFICHAGE)
VALUES (ROLE_ID_SEQ.nextval, 'CST_E', 'Examinateur (CST)', 'SyGAL::CST_E', 1, 'Examinateur (CST)', 1, 1, 1, 'zzzz_1');
INSERT INTO ROLE (ID, CODE, LIBELLE, SOURCE_CODE, SOURCE_ID, ROLE_ID, THESE_DEP, HISTO_CREATEUR_ID, HISTO_MODIFICATEUR_ID, ORDRE_AFFICHAGE)
VALUES (ROLE_ID_SEQ.nextval, 'CST_O', 'Observateur (CST)', 'SyGAL::CST_O', 1, 'Observateur (CST)', 1, 1, 1, 'zzzz_2');

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (CATEGORIE_PRIVILEGE_ID_SEQ.nextval, 'CS_comite', 'Comité suivi - Gestion des comités de suivi', 2000);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteSuivi_afficher', 'Afficher les comités de suivi', 100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteSuivi_ajouter', 'Ajouter les comités de suivi', 200);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteSuivi_modifier', 'Modifier les comités de suivi', 300);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteSuivi_historiser', 'Historiser les comités de suivi', 400);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteSuivi_supprimer', 'Supprimer les comités de suivi', 500);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteSuivi_valider', 'Valider les comités de suivi', 600);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (CATEGORIE_PRIVILEGE_ID_SEQ.nextval, 'CS_membre', 'Comité suivi - Gestion des membres', 2100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteMembre_afficher', 'Afficher les membres d''un comité', 100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteMembre_ajouter', 'Ajouter un membre à un comité', 200);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteMembre_modifier', 'Modifier un membre d''un comité', 300);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteMembre_historiser', 'Historiser un membre d''un comité', 400);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteMembre_supprimer', 'Supprimer un membre d''un comité', 500);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteMembre_lier', 'Lier un membre d''un comité', 600);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (CATEGORIE_PRIVILEGE_ID_SEQ.nextval, 'CS_compterendu', 'Comité suivi - Gestion des comptes rendus', 2200);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteRapport_afficher', 'Afficher les rapports d''un comité', 100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteRapport_ajouter', 'Ajouter un rapport à un comité', 200);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteRapport_modifier', 'Modifier un rapport d''un comité', 300);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteRapport_historiser', 'Historiser un rapport d''un comité', 400);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (PRIVILEGE_ID_SEQ.nextval, CATEGORIE_PRIVILEGE_ID_SEQ.currval, 'ComiteRapport_supprimer', 'Supprimer un rapport d''un comité', 500);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (CATEGORIE_PRIVILEGE_ID_SEQ.nextval, 'CS_notification', 'Comité suivi - Gestion des notifications', 2300);

--create sequence  TYPE_VALIDATION_ID_SEQ;
--select TYPE_VALIDATION_ID_SEQ.currval from dual;
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (TYPE_VALIDATION_ID_SEQ.nextval, 'FINALISATION_COMITE_SUIVI', 'Finalisation du comité de suivi de thèse');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (TYPE_VALIDATION_ID_SEQ.nextval, 'VALIDATION_COMITE_SUIVI', 'Validation du comité de suivi de thèse');

--create sequence  NATURE_FICHIER_ID_SEQ;
--select NATURE_FICHIER_ID_SEQ.currval from dual;
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (NATURE_FICHIER_ID_SEQ.nextval, 'CODE_COMPTE_RENDU_COMITE_SUIVI', 'Compte rendu d''un comité de suivi de thèse');