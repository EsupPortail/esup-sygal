-- Nouvelle nature de fichier pour l'attestation de responsabilité civile du doctorant
INSERT INTO nature_fichier (id, code, libelle)
VALUES (nextval('nature_fichier_id_seq'), 'ADMISSION_ATTESTATION_RESPONSABILITE_CIVILE', 'Attestation de responsabilité civile du doctorant');