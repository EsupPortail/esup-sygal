-- MACRO

INSERT INTO categorie_privilege (code, libelle, ordre)
VALUES ('documentmacro', 'UnicaenRenderer - Gestion des macros', 10000);

INSERT INTO privilege(id, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'documentmacro_index', 'Afficher l''index des macros', 1 UNION
    SELECT 'documentmacro_ajouter', 'Ajouter une macro', 10 UNION
    SELECT 'documentmacro_modifier', 'Modifier une macro', 20 UNION
    SELECT 'documentmacro_supprimer', 'Supprimer une macro', 40
)
SELECT nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
FROM d
JOIN categorie_privilege cp ON cp.CODE = 'documentmacro';

-- TEMPLATE

INSERT INTO categorie_privilege (code, libelle, ordre)
VALUES ('documenttemplate', 'UnicaenRenderer - Gestion des templates', 10010);

INSERT INTO privilege(id, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'documenttemplate_index', 'Afficher l''index des contenus', 1 UNION
    SELECT 'documenttemplate_modifier', 'Modifier un contenu', 20 UNION
    SELECT 'documenttemplate_supprimer', 'Supprimer un contenu', 40 UNION
    SELECT 'documenttemplate_ajouter', 'Ajouter un contenu', 15 UNION
    SELECT 'documenttemplate_afficher', 'Afficher un template', 10
)
SELECT nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
FROM d
JOIN categorie_privilege cp ON cp.CODE = 'documenttemplate';

-- CONTENU

INSERT INTO categorie_privilege (code, libelle, ordre)
VALUES ('documentcontenu', 'UnicaenRenderer - Gestion des contenus', 10020);

INSERT INTO privilege(id, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
WITH d(code, lib, ordre) AS (
    SELECT 'documentcontenu_index', 'Accès à l''index des contenus', 10 UNION
    SELECT 'documentcontenu_afficher', 'Afficher un contenu', 20 UNION
    SELECT 'documentcontenu_supprimer', 'Supprimer un contenu ', 30
)
SELECT nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
FROM d
JOIN categorie_privilege cp ON cp.CODE = 'documentcontenu';