-- Changement de la taille de la colonne
ALTER TABLE admission_etudiant
    ALTER COLUMN adresse_cp_ville_etrangere TYPE VARCHAR(38)

-- Renommage de la validation
UPDATE admission_type_validation
SET libelle = 'Validation par les gestionnaires'
WHERE code = 'VALIDATION_GESTIONNAIRE';

-- Macro Renderer
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('Url#AccueilAdmission', '<p>Permet de récupérer l''url de l''accueil du module admission</p>', 'Url',
        'getAccueilAdmission');
