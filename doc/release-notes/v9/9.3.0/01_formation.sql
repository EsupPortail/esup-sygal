alter table formation_formation add fiche_id bigint references fichier (id);
INSERT INTO nature_fichier (id, code, libelle) VALUES (nextval('nature_fichier_id_seq'::regclass), 'FORMATION_FICHE', 'Fiche PDF détaillée d''une formation');
