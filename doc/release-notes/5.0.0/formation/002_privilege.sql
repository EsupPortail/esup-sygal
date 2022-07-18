
create sequence if not exists categorie_privilege_id_seq;
alter table categorie_privilege alter column id set default nextval('categorie_privilege_id_seq');
alter sequence categorie_privilege_id_seq owned by categorie_privilege.id;

alter sequence categorie_privilege_id_seq restart with 5000;

-- INDEX --------------------------------------------------------------------------------------------------------------
INSERT INTO categorie_privilege (code, libelle, ordre)
VALUES ('formation', 'Module de formation', 5000);

INSERT INTO privilege(id, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'index', 'Accès à l''index du module de formation', 1 UNION
    SELECT 'index_doctorant', 'Accès à l''index doctorant', 2 UNION
    SELECT 'index_formateur', 'Accès à l''index des formateurs', 3
)
SELECT nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
FROM d
JOIN categorie_privilege cp ON cp.CODE = 'formation';


-- MODULE --------------------------------------------------------------------------------------------------------------

INSERT INTO categorie_privilege (code, libelle, ordre)
VALUES ('formation_module', 'Gestion des modules de formations', 5100);

INSERT INTO privilege(id, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'index', 'Accès à l''index des modules de formation', 1 UNION
    SELECT 'afficher', 'Afficher un module de formation', 2 UNION
    SELECT 'ajouter', 'Ajouter un module de formation', 3 UNION
    SELECT 'modifier', 'Modifier un module de formation', 4 UNION
    SELECT 'historiser', 'Historiser/Restaurer un module de formation', 5 UNION
    SELECT 'supprimer', 'Supprimer un module de formation', 6 UNION
    SELECT 'catalogue', 'Accéder au catalogue des formations ', 7
)
SELECT nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
FROM d
         JOIN categorie_privilege cp ON cp.CODE = 'formation_module';

-- FORMATION -----------------------------------------------------------------------------------------------------------

INSERT INTO categorie_privilege (code, libelle, ordre)
VALUES ('formation_formation', 'Gestion des formations', 5200);

INSERT INTO privilege(id, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'index', 'Accès à l''index des actions de formation', 1 UNION
    SELECT 'afficher', 'Afficher une action de formation', 2 UNION
    SELECT 'ajouter', 'Ajouter une action de formation', 3 UNION
    SELECT 'modifier', 'Modifier une action de formation', 4 UNION
    SELECT 'historiser', 'Historiser/Restaurer une action de formation', 5 UNION
    SELECT 'supprimer', 'Supprimer une action de formation', 6
)
SELECT nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
FROM d
     JOIN categorie_privilege cp ON cp.CODE = 'formation_formation';
-- SESSION -------------------------------------------------------------------------------------------------------------

INSERT INTO privilege(id, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'index', 'Accès à l''index des modules de formation', 1 UNION
    SELECT 'afficher', 'Afficher un module de formation', 2 UNION
    SELECT 'ajouter', 'Ajouter un module de formation', 3 UNION
    SELECT 'modifier', 'Modifier un module de formation', 4 UNION
    SELECT 'historiser', 'Historiser/Restaurer un module de formation', 5 UNION
    SELECT 'supprimer', 'Supprimer un module de formation', 6 UNION
    SELECT 'catalogue', 'Accéder au catalogue des formations ', 7
)
SELECT nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
FROM d
JOIN categorie_privilege cp ON cp.CODE = 'formation_formation';

INSERT INTO categorie_privilege (code, libelle, ordre)
VALUES ('formation_session', 'Gestion des sessions de formations', 5300);

-- SEANCE --------------------------------------------------------------------------------------------------------------

INSERT INTO privilege(id, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'index', 'Accès à l''index des sessions de formation', 1 UNION
    SELECT 'afficher', 'Afficher une session de formation', 2 UNION
    SELECT 'ajouter', 'Ajouter une session de formation', 3 UNION
    SELECT 'modifier', 'Modifier une session de formation', 4 UNION
    SELECT 'historiser', 'Historiser/Restaurer une session de formation', 5 UNION
    SELECT 'supprimer', 'Supprimer une session de formation', 6 UNION
    SELECT 'gerer_inscription', 'Gerer les inscriptions d''une session de formation', 7
)
SELECT nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
FROM d
JOIN categorie_privilege cp ON cp.CODE = 'formation_session';

INSERT INTO categorie_privilege (code, libelle, ordre)
VALUES ('formation_seance', 'Gestion des séances de formations', 5400);

-- INSCRIPTION ---------------------------------------------------------------------------------------------------------

INSERT INTO categorie_privilege (code, libelle, ordre)
VALUES ('formation_inscription', 'Gestion des inscriptions aux formations', 5500);

INSERT INTO privilege(id, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'index', 'Accès à l''index des inscriptions de formation', 1 UNION
    SELECT 'afficher', 'Afficher une inscription de formation', 2 UNION
    SELECT 'ajouter', 'Ajouter une inscription de formation', 3 UNION
    SELECT 'historiser', 'Historiser/Restaurer une inscription de formation', 5 UNION
    SELECT 'supprimer', 'Supprimer une inscription de formation', 6 UNION
    SELECT 'gerer_liste', 'Gerer la liste d''une inscription', 7 UNION
    SELECT 'generer_convocation', 'Generer la convoctation', 8 UNION
    SELECT 'generer_attestation', 'Gerer l''attestation', 9
)
SELECT nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
FROM d
JOIN categorie_privilege cp ON cp.CODE = 'formation_inscription';

-- ENQUETE -------------------------------------------------------------------------------------------------------------

INSERT INTO categorie_privilege (code, libelle, ordre)
VALUES ('formation_enquete', 'Gestion de l''enquête', 5600);

INSERT INTO privilege(id, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'question_afficher', 'Afficher les questions de l''enquête', 2 UNION
    SELECT 'question_ajouter', 'Ajouter une question de l''enquête', 3 UNION
    SELECT 'question_modifier', 'Modifier une question de l''enquête', 3 UNION
    SELECT 'question_historiser', 'Historiser/Restaurer une question de l''enquête', 5 UNION
    SELECT 'question_supprimer', 'Supprimer une question de l''enquête', 6 UNION
    SELECT 'reponse_repondre', 'Répondre à l''enquête', 8 UNION
    SELECT 'reponse_resultat', 'Afficher les résultats de l''enquête', 9
)
SELECT nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
FROM d
JOIN categorie_privilege cp ON cp.CODE = 'formation_enquete';