INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (4000, 'formation', 'Module de formation', 3000);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (800, 4000, 'index', 'Accès à l''index du module de formation',1);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (4001, 'formation_module', 'Gestion des modules de formations', 3100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (801, 4001, 'index', 'Accès à l''index des modules de formation', 1);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (802, 4001, 'afficher', 'Afficher un module de formation', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (803, 4001, 'ajouter', 'Ajouter un module de formation', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (804, 4001, 'modifier', 'Modifier un module de formation', 4);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (805, 4001, 'historiser', 'Historiser/Restaurer un module de formation', 5);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (806, 4001, 'supprimer', 'Supprimer un module de formation', 6);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (4002, 'formation_session', 'Gestion des sessions de formations', 3200);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (811, 4002, 'index', 'Accès à l''index des sessions de formation', 1);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (812, 4002, 'afficher', 'Afficher une session de formation', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (813, 4002, 'ajouter', 'Ajouter une session de formation', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (814, 4002, 'modifier', 'Modifier une session de formation', 4);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (815, 4002, 'historiser', 'Historiser/Restaurer une session de formation', 5);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (816, 4002, 'supprimer', 'Supprimer une session de formation', 6);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (817, 4002, 'gerer_inscription', 'Gerer les inscriptions d''une session de formation', 7);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (4003, 'formation_seance', 'Gestion des séances de formations', 3300);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (821, 4003, 'index', 'Accès à l''index des séances de formation', 1);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (822, 4003, 'afficher', 'Afficher une séance de formation', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (823, 4003, 'ajouter', 'Ajouter une séance de formation', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (824, 4003, 'modifier', 'Modifier une séance de formation', 4);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (825, 4003, 'historiser', 'Historiser/Restaurer une séance de formation', 5);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (826, 4003, 'supprimer', 'Supprimer une séance de formation', 6);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (827, 4003, 'renseigner_presence', 'Renseigner les présences', 7);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (4004, 'formation_inscription', 'Gestion des inscriptions aux formations', 3400);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (831, 4004, 'index', 'Accès à l''index des inscriptions de formation', 1);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (832, 4004, 'afficher', 'Afficher une inscription de formation', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (833, 4004, 'ajouter', 'Ajouter une inscription de formation', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (835, 4004, 'historiser', 'Historiser/Restaurer une inscription de formation', 5);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (836, 4004, 'supprimer', 'Supprimer une inscription de formation', 6);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (837, 4004, 'gerer_liste', 'Gerer la liste d''une inscription', 7);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (838, 4004, 'generer_convocation', 'Generer la convoctation', 8);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (839, 4004, 'generer_attestation', 'Gerer l''attestation', 9);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (4005, 'formation_enquete', 'Gestion de l''enquête', 3500);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (842, 4005, 'question_afficher', 'Afficher les questions de l''enquête', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (843, 4005, 'question_ajouter', 'Ajouter une question de l''enquête', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (844, 4005, 'question_modifier', 'Modifier une question de l''enquête', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (845, 4005, 'question_historiser', 'Historiser/Restaurer une question de l''enquête', 5);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (846, 4005, 'question_supprimer', 'Supprimer une question de l''enquête', 6);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (848, 4005, 'reponse_repondre', 'Répondre à l''enquête', 8);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (849, 4005, 'reponse_resultat', 'Afficher les résultats de l''enquête', 9);