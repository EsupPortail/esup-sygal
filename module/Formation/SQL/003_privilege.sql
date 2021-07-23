INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (nextval('categorie_privilege_id_seq'), 'formation2', 'Module de formation', 3000);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'index', 'Accès à l''index du module de formation',1);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'index_doctorant', 'Accès à l''index doctorant',2);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (nextval('categorie_privilege_id_seq'), 'formation_module2', 'Gestion des modules de formations', 3100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'index', 'Accès à l''index des modules de formation', 1);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'afficher', 'Afficher un module de formation', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'ajouter', 'Ajouter un module de formation', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'modifier', 'Modifier un module de formation', 4);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'historiser', 'Historiser/Restaurer un module de formation', 5);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'supprimer', 'Supprimer un module de formation', 6);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (nextval('categorie_privilege_id_seq'), 'formation_session2', 'Gestion des sessions de formations', 3200);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'index', 'Accès à l''index des sessions de formation', 1);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'afficher', 'Afficher une session de formation', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'ajouter', 'Ajouter une session de formation', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'modifier', 'Modifier une session de formation', 4);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'historiser', 'Historiser/Restaurer une session de formation', 5);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'supprimer', 'Supprimer une session de formation', 6);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'gerer_inscription', 'Gerer les inscriptions d''une session de formation', 7);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (nextval('categorie_privilege_id_seq'), 'formation_seance2', 'Gestion des séances de formations', 3300);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'index', 'Accès à l''index des séances de formation', 1);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'afficher', 'Afficher une séance de formation', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'ajouter', 'Ajouter une séance de formation', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'modifier', 'Modifier une séance de formation', 4);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'historiser', 'Historiser/Restaurer une séance de formation', 5);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'supprimer', 'Supprimer une séance de formation', 6);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'renseigner_presence', 'Renseigner les présences', 7);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (nextval('categorie_privilege_id_seq'), 'formation_inscription2', 'Gestion des inscriptions aux formations', 3400);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'index', 'Accès à l''index des inscriptions de formation', 1);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'afficher', 'Afficher une inscription de formation', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'ajouter', 'Ajouter une inscription de formation', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'historiser', 'Historiser/Restaurer une inscription de formation', 5);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'supprimer', 'Supprimer une inscription de formation', 6);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'gerer_liste', 'Gerer la liste d''une inscription', 7);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'generer_convocation', 'Generer la convoctation', 8);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'generer_attestation', 'Gerer l''attestation', 9);

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (nextval('categorie_privilege_id_seq'), 'formation_enquete2', 'Gestion de l''enquête', 3500);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'question_afficher', 'Afficher les questions de l''enquête', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'question_ajouter', 'Ajouter une question de l''enquête', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'question_modifier', 'Modifier une question de l''enquête', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'question_historiser', 'Historiser/Restaurer une question de l''enquête', 5);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'question_supprimer', 'Supprimer une question de l''enquête', 6);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'reponse_repondre', 'Répondre à l''enquête', 8);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (nextval('privilege_id_seq'), currval('categorie_privilege_id_seq'), 'reponse_resultat', 'Afficher les résultats de l''enquête', 9);