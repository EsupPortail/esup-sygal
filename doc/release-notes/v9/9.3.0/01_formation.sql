alter table formation_formation add fiche_id bigint references fichier (id);
INSERT INTO nature_fichier (id, code, libelle) VALUES (nextval('nature_fichier_id_seq'::regclass), 'FORMATION_FICHE', 'Fiche PDF détaillée d''une formation');

update formation_etat set description = 'La session est imminente' where code = 'I';
update formation_etat set description = 'Les inscriptions sont ouvertes' where code = 'O';
update formation_etat set description = 'Les inscriptions sont à présent fermées' where code = 'F'