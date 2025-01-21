alter table formation_formation add fiche_id bigint references fichier (id);
INSERT INTO nature_fichier (id, code, libelle) VALUES (nextval('nature_fichier_id_seq'::regclass), 'FORMATION_FICHE', 'Fiche PDF détaillée d''une formation');

update formation_etat set description = 'La session est imminente' where code = 'I';
update formation_etat set description = 'Les inscriptions sont ouvertes' where code = 'O';
update formation_etat set description = 'Les inscriptions sont à présent fermées' where code = 'F'

--
-- Template Renderer (Envoi de la liste des inscrits à une session au(x) formateur(s))
--
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Url#ListeInscritsSessionFormation', '<p>Fourni l''url vers la liste des inscrits à une session de formation</p>', 'Url', 'getUrlListeInscritsSessionFormation');
INSERT INTO unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css, namespace, engine) VALUES ('FORMATION_TRANSMETTRE_LISTE_INSCRITS_FORMATEURS', '<p>Mail envoyé aux formateurs afin qu''ils accèdent aux inscrits d''une de leur session </p>', 'mail', 'Liste des inscrits à la session de formation VAR[Formation#Libelle]', e'<p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 1rem; color: #212529; font-family: ubuntu, arial, sans-serif; font-size: 12px;"><span style="color: #212529; font-family: ubuntu, arial, sans-serif;"><span style="font-size: 12px;">Bonjour, </span></span></p>
<p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 1rem; color: #212529; font-family: ubuntu, arial, sans-serif; font-size: 12px;"><span style="color: #212529; font-family: ubuntu, arial, sans-serif;"><span style="font-size: 12px;">Voici la liste des inscrits à la formation VAR[Formation#Libelle] dont vous êtes déclaré·e comme formateur·trice. </span></span></p>
<p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 1rem; color: #212529; font-family: ubuntu, arial, sans-serif; font-size: 12px;"><span style="color: #212529; font-family: ubuntu, arial, sans-serif;"><span style="font-size: 12px;">Afin d''y accéder, veuillez cliquer sur ce lien : VAR[Url#ListeInscritsSessionFormation] </span></span></p>
<p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 1rem; color: #212529; font-family: ubuntu, arial, sans-serif; font-size: 12px;"><span style="color: #212529; font-family: ubuntu, arial, sans-serif;"><span style="font-size: 12px;">Pour rappel, les séances de cette formation se tiendront : VAR[Session#SeancesTable] </span></span></p>
<p style="box-sizing: border-box; margin-top: 0px; margin-bottom: 1rem; color: #212529; font-family: ubuntu, arial, sans-serif; font-size: 12px;"><span style="color: #212529; font-family: ubuntu, arial, sans-serif;"><span style="font-size: 12px;">Cordialement, VAR[Formation#Responsable]</span></span></p>', null, 'Formation\Provider\Template', 'default');
