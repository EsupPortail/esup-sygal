-- MACRO

INSERT INTO unicaen_privilege_categorie (id ,code, libelle, ordre, namespace) VALUES (next_val('unicaen_privilege_categorie_id_seq'), 'documentmacro', 'UnicaenRenderer - Gestion des macros', 11010, 'UnicaenRenderer\Provider\Privilege');
INSERT INTO unicaen_privilege_privilege (id, categorie_id, code, libelle, ordre) VALUES (next_val('unicaen_privilege_privilege_id_seq'), current_val('unicaen_privilege_categorie_id_seq'), 'documentmacro_index', 'Afficher l''index des macros', 1);
INSERT INTO unicaen_privilege_privilege (id, categorie_id, code, libelle, ordre) VALUES (next_val('unicaen_privilege_privilege_id_seq'), current_val('unicaen_privilege_categorie_id_seq'), 'documentmacro_ajouter', 'Ajouter une macro', 10);
INSERT INTO unicaen_privilege_privilege (id, categorie_id, code, libelle, ordre) VALUES (next_val('unicaen_privilege_privilege_id_seq'), current_val('unicaen_privilege_categorie_id_seq'), 'documentmacro_modifier', 'Modifier une macro', 20);
INSERT INTO unicaen_privilege_privilege (id, categorie_id, code, libelle, ordre) VALUES (next_val('unicaen_privilege_privilege_id_seq'), current_val('unicaen_privilege_categorie_id_seq'), 'documentmacro_supprimer', 'Supprimer une macro', 40);

-- TEMPLATE

INSERT INTO unicaen_privilege_categorie (id, code, libelle, ordre, namespace) VALUES (next_val('unicaen_privilege_categorie_id_seq'), 'documenttemplate', 'UnicaenRenderer - Gestion des templates', 11020, 'UnicaenRenderer\Provider\Privilege');
INSERT INTO unicaen_privilege_privilege (id, categorie_id, code, libelle, ordre) VALUES (next_val('unicaen_privilege_privilege_id_seq'), current_val('unicaen_privilege_categorie_id_seq'), 'documenttemplate_index', 'Afficher l''index des contenus', 1);
INSERT INTO unicaen_privilege_privilege (id, categorie_id, code, libelle, ordre) VALUES (next_val('unicaen_privilege_privilege_id_seq'), current_val('unicaen_privilege_categorie_id_seq'), 'documenttemplate_modifier', 'Modifier un contenu', 20);
INSERT INTO unicaen_privilege_privilege (id, categorie_id, code, libelle, ordre) VALUES (next_val('unicaen_privilege_privilege_id_seq'), current_val('unicaen_privilege_categorie_id_seq'), 'documenttemplate_supprimer', 'Supprimer un contenu', 40);
INSERT INTO unicaen_privilege_privilege (id, categorie_id, code, libelle, ordre) VALUES (next_val('unicaen_privilege_privilege_id_seq'), current_val('unicaen_privilege_categorie_id_seq'), 'documenttemplate_ajouter', 'Ajouter un contenu', 15);
INSERT INTO unicaen_privilege_privilege (id, categorie_id, code, libelle, ordre) VALUES (next_val('unicaen_privilege_privilege_id_seq'), current_val('unicaen_privilege_categorie_id_seq'), 'documenttemplate_afficher', 'Afficher un template', 10);

-- CONTENU

INSERT INTO unicaen_privilege_categorie (id, code, libelle, ordre, namespace) VALUES (next_val('unicaen_privilege_categorie_id_seq'), 'documentcontenu', 'UnicaenRenderer - Gestion des contenus', 11030, 'UnicaenRenderer\Provider\Privilege');
INSERT INTO unicaen_privilege_privilege (id, categorie_id, code, libelle, ordre) VALUES (next_val('unicaen_privilege_privilege_id_seq'), current_val('unicaen_privilege_categorie_id_seq'), 'documentcontenu_index', 'Accès à l''index des contenus', 10);
INSERT INTO unicaen_privilege_privilege (id, categorie_id, code, libelle, ordre) VALUES (next_val('unicaen_privilege_privilege_id_seq'), current_val('unicaen_privilege_categorie_id_seq'), 'documentcontenu_afficher', 'Afficher un contenu', 20);
INSERT INTO unicaen_privilege_privilege (id, categorie_id, code, libelle, ordre) VALUES (next_val('unicaen_privilege_privilege_id_seq'), current_val('unicaen_privilege_categorie_id_seq'), 'documentcontenu_supprimer', 'Supprimer un contenu ', 30);