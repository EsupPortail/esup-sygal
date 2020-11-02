-- GERER LES PROFILS MANQUANTS --> RAPPORTEUR ABSENT

INSERT INTO CATEGORIE_PRIVILEGE (ID, CODE, LIBELLE, ORDRE) VALUES (101, 'soutenance', 'Soutenance', 1000);

INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (524, 101, 'proposition-sursis', 'Accorder un sursis pour la validation d''une Modifier la proposition de soutenance', 500);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (517, 101, 'proposition-presidence', 'Générer document pour la signature par la présidence', 34);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (518, 101, 'avis-annuler', 'Annuler un avis de soutenance', 400);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (519, 101, 'avis-notifier', 'Notifier les demandes d''avis de soutenance', 500);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (520, 101, 'index-global', 'Accès à l''index global', 1);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (521, 101, 'index-acteur', 'Accès à l''index acteur', 2);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (500, 101, 'modification-date-rapport', 'Modification de la date de rendu des rapports', 50);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (501, 101, 'association-membre-individu', 'Associer des individus aux membres de jury', 50);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (502, 101, 'proposition-visualisation', 'Visualier de la proposition de soutenance', 10);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (503, 101, 'proposition-modification', 'Modifier la proposition de soutenance', 20);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (504, 101, 'proposition-validation-acteur', 'Valider/Annuler la proposition de soutenance (acteur)', 30);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (505, 101, 'proposition-validation-ed', 'Valider/Annuler la proposition de soutenance (ecole-doctorale)', 31);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (506, 101, 'proposition-validation-ur', 'Valider/Annuler la proposition de soutenance (unite-recherche)', 32);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (507, 101, 'proposition-validation-bdd', 'Valider/Annuler la proposition de soutenance (bureau-doctorat)', 33);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (508, 101, 'engagement-impartialite-signer', 'Signer l''engagement d''impartialité ', 130);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (509, 101, 'engagement-impartialite-annuler', 'Annuler une signature  d''engagement d''impartialité', 140);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (510, 101, 'engagement-impartialite-visualiser', 'Visualiser un engagement d''impartialité', 100);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (511, 101, 'engagement-impartialite-notifier', 'Notifier des demandes de signature d''engagement d''impartialité', 110);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (512, 101, 'presoutenance-visualisation', 'Visualiser les informations associées à la présoutenance', 45);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (513, 101, 'avis-visualisation', 'Visualiser l''avis de soutenance et au rapport de présoutenance', 200);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (514, 101, 'avis-modifier', 'Modifier l''avis de soutenance et le rapport de présoutenance', 300);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (522, 101, 'index-rapporteur', 'Accès à l''index rapporteur', 3);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (515, 101, 'qualite-visualisation', 'Visualiser les qualités renseignées', 400);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (516, 101, 'qualite-modification', 'Ajout/Modification des qualités ', 410);
INSERT INTO PRIVILEGE (ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE) VALUES (523, 101, 'index-structure', 'Accès à l''index structure', 4);