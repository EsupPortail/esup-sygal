-- TODO SEQUENCE STUFF ...

-------------------------------------------------------------------------------
--- ETAT
-------------------------------------------------------------------------------

INSERT INTO SOUTENANCE_ETAT (ID, CODE, LIBELLE) VALUES (1, 'EN_COURS', 'En cours d''examen');
INSERT INTO SOUTENANCE_ETAT (ID, CODE, LIBELLE) VALUES (2, 'VALIDEE', 'Soutenance autorisée');
INSERT INTO SOUTENANCE_ETAT (ID, CODE, LIBELLE) VALUES (3, 'REJETEE', 'Soutenance rejetée');

-------------------------------------------------------------------------------
--- QUALITE
-------------------------------------------------------------------------------

INSERT INTO SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (0, 'Qualité inconnue', 'B', 'N', 'N');
INSERT INTO SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (1, 'Professeur des universités', 'A', 'O', 'N');
INSERT INTO SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (2, 'Directeur de recherche', 'A', 'O', 'N');
INSERT INTO SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (4, 'Maître de conférences', 'B', 'N', 'N');
INSERT INTO SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (5, 'Chargé de recherche', 'B', 'N', 'N');
INSERT INTO SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (6, 'Maître de conférences (HDR)', 'B', 'O', 'N');
INSERT INTO SOUTENANCE_QUALITE (ID, LIBELLE, RANG, HDR, EMERITAT) VALUES (7, 'Professeur émerite', 'A', 'O', 'O');

INSERT INTO SOUTENANCE_QUALITE_SUP (ID, QUALITE_ID, LIBELLE, HISTO_CREATION, HISTO_CREATEUR_ID, HISTO_MODIFICATION, HISTO_MODIFICATEUR_ID, HISTO_DESTRUCTION, HISTO_DESTRUCTEUR_ID) VALUES (1, 5, 'CHARGE DE RECHERCHE', TO_DATE('2019-12-17 10:01:15', 'YYYY-MM-DD HH24:MI:SS'), 1, TO_DATE('2019-12-17 10:01:22', 'YYYY-MM-DD HH24:MI:SS'), 1, null, null);
INSERT INTO SOUTENANCE_QUALITE_SUP (ID, QUALITE_ID, LIBELLE, HISTO_CREATION, HISTO_CREATEUR_ID, HISTO_MODIFICATION, HISTO_MODIFICATEUR_ID, HISTO_DESTRUCTION, HISTO_DESTRUCTEUR_ID) VALUES (6, 1, 'PROFESSEUR DES UNIVERSITES', TO_DATE('2019-12-17 11:54:12', 'YYYY-MM-DD HH24:MI:SS'), 1, TO_DATE('2019-12-17 11:54:12', 'YYYY-MM-DD HH24:MI:SS'), 1, null, null);
INSERT INTO SOUTENANCE_QUALITE_SUP (ID, QUALITE_ID, LIBELLE, HISTO_CREATION, HISTO_CREATEUR_ID, HISTO_MODIFICATION, HISTO_MODIFICATEUR_ID, HISTO_DESTRUCTION, HISTO_DESTRUCTEUR_ID) VALUES (2, 2, 'DIRECTEUR DE RECHERCHE', TO_DATE('2019-12-17 11:26:10', 'YYYY-MM-DD HH24:MI:SS'), 1, TO_DATE('2019-12-17 11:26:10', 'YYYY-MM-DD HH24:MI:SS'), 1, null, null);
INSERT INTO SOUTENANCE_QUALITE_SUP (ID, QUALITE_ID, LIBELLE, HISTO_CREATION, HISTO_CREATEUR_ID, HISTO_MODIFICATION, HISTO_MODIFICATEUR_ID, HISTO_DESTRUCTION, HISTO_DESTRUCTEUR_ID) VALUES (3, 1, 'PROFESSEUR DES UNIVERSITÉS', TO_DATE('2019-12-17 11:27:29', 'YYYY-MM-DD HH24:MI:SS'), 1, TO_DATE('2019-12-17 11:27:29', 'YYYY-MM-DD HH24:MI:SS'), 1, null, null);

-------------------------------------------------------------------------------
--- NATURE DOCUMENT
-------------------------------------------------------------------------------

INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (11, 'JUSTIFICATIF_HDR', 'Justificatif d''habilitation à diriger des recherches');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (12, 'DELOCALISATION_SOUTENANCE', 'Formulaire de délocalisation de soutenance');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (13, 'DELEGUATION_SIGNATURE', 'Formulaire de délégation de signature du rapport de soutenance (visioconférence)');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (14, 'DEMANDE_LABEL_EUROPEEN', 'Formulaire de demande de label européen');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (15, 'DEMANDE_LANGUE_ANGLAISE', 'Formulaire de demande de manuscrit ou de soutenance en anglais');
INSERT INTO NATURE_FICHIER (ID, CODE, LIBELLE) VALUES (16, 'JUSTIFICATIF_EMERITAT', 'Justificatif d''émeritat');

-------------------------------------------------------------------------------
--- TYPE VALIDATION
-------------------------------------------------------------------------------

INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (6, 'PROPOSITION_SOUTENANCE', 'Validation de la proposition de soutenance');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (8, 'ENGAGEMENT_IMPARTIALITE', 'Signature de l''engagement d''impartialité');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (9, 'VALIDATION_PROPOSITION_ED', 'Validation de la proposition de soutenance par l''école doctorale');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (10, 'VALIDATION_PROPOSITION_UR', 'Validation de la proposition de soutenance par l''unité de recherche');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (11, 'VALIDATION_PROPOSITION_BDD', 'Validation de la proposition de soutenance par le bureau des doctorats');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (12, 'AVIS_SOUTENANCE', 'Signature de l''avis de soutenance');
INSERT INTO TYPE_VALIDATION (ID, CODE, LIBELLE) VALUES (13, 'REFUS_ENGAGEMENT_IMPARTIALITE', 'Refus de l''engagement d''impartialité');