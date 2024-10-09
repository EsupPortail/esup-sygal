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

-- ajout de la colonne pour le financement complémentaire
alter table admission_financement
    ADD column if not exists financement_compl_id bigint REFERENCES origine_financement (id);

-- déplacement du numero_candidat de admission_etudiant vers admission_admission
ALTER TABLE admission_admission
    ADD COLUMN numero_candidat VARCHAR(10);

UPDATE admission_admission aa
SET numero_candidat = ae.numero_candidat
    FROM admission_etudiant ae
WHERE aa.id = ae.admission_id;

ALTER TABLE admission_etudiant
DROP COLUMN numero_candidat;