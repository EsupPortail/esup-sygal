-- ROLE ----------------------------------------------------------------------------------------------------------------

INSERT INTO role (id, code, libelle, source_code, source_id, role_id, is_default, ldap_filter, attrib_auto, these_dep, type_structure_dependant_id, ordre_affichage, histo_createur_id)
select nextval('role_id_seq'), 'FORMATEUR', 'Formateur·trice', 'SYGAL::FORMATEUR', 1, 'Formateur·trice', false, null, false, false, 1, 'formation_aab', 1;
INSERT INTO role (id, code, libelle, source_code, source_id, role_id, is_default, ldap_filter, attrib_auto, these_dep, type_structure_dependant_id, ordre_affichage, histo_createur_id)
select nextval('role_id_seq'), 'GEST_FORMATION', 'Gestionnaire de formation', 'SYGAL::GEST_FORMATION', 1, 'Gestionnaire de formation', false, null, false, false, 1, 'formation_aaa', 1;

-- NATURE DE DOCUMENT --------------------------------------------------------------------------------------------------

INSERT INTO nature_fichier (id, code, libelle) VALUES (nextval('nature_fichier_id_seq'), 'SIGNATURE_FORMATION', 'Signature pour les formations');

-- ETAT DE FORMATION ---------------------------------------------------------------------------------------------------

INSERT INTO FORMATION_ETAT (CODE, LIBELLE, DESCRIPTION, ICONE, COULEUR, ORDRE) VALUES ('P', 'En préparation', 'Formation en cours de préparation', 'glyphicon glyphicon-time', '#cc9200', 1);
INSERT INTO FORMATION_ETAT (CODE, LIBELLE, DESCRIPTION, ICONE, COULEUR, ORDRE) VALUES ('O', 'Inscription ouverte', null, 'glyphicon glyphicon-ok-circle', '#11cc00', 2);
INSERT INTO FORMATION_ETAT (CODE, LIBELLE, DESCRIPTION, ICONE, COULEUR, ORDRE) VALUES ('F', 'Inscription fermée', null, 'glyphicon glyphicon-ban-circle', '#008fcc', 3);
INSERT INTO FORMATION_ETAT (CODE, LIBELLE, DESCRIPTION, ICONE, COULEUR, ORDRE) VALUES ('C', 'Close', 'Formation close', null, null, 4);
INSERT INTO FORMATION_ETAT (CODE, LIBELLE, DESCRIPTION, ICONE, COULEUR, ORDRE) VALUES ('A', 'Session annulée', 'La session a été annulée', 'icon icon-historiser', '#cc0000', 5);

-- MACRO ---------------------------------------------------------------------------------------------------------------

INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Doctorant#Denomination', '<p>Retourne la dénomination du doctorant</p>', 'doctorant', '__toString');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Module#Libelle', '<p>Retourne le libellé du module de formation</p>', 'module', 'getLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Formation#Libelle', '<p>Retourne le libellé de la formation</p>', 'formation', 'getLibelle');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Session#Modalite', '<p>Retourne la modalité de la formation sous la forme : présentielle ou distancielle.</p>', 'session', 'getModalite');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Formation#Responsable', '<p>Retourne la dénomination du responsable de la formation</p>', 'formation', 'toStringResponsable');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Session#SeancesTable', '<p>Retourne la liste des séances sous la forme d''un tableau HTML</p>', 'session', 'getSeancesAsTable');
INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name) VALUES ('Session#Durée', '<p>Retourne la durée totale sous la forme d''un flottant (par exemple : 5,75)</p>', 'session', 'getDuree');

-- TEMPLATE ------------------------------------------------------------------------------------------------------------

INSERT INTO unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css) VALUES ('FORMATION_INSCRIPTION_ENREGISTREE', '<p>Mail envoyé au doctorant·e lors d''une inscription à une session de formation</p>', 'mail', 'Validation de votre inscription à la session de formation VAR[Formation#Libelle]', '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>Nous avons bien reçu votre demande d’inscription à la formation VAR[Formation#Libelle] se déroulant : <br />VAR[Session#SeancesTable]</p>
<p><br />Cordialement,<br />VAR[Formation#Responsable]<br /><br style="background-color: #2b2b2b; color: #a9b7c6; font-family: ''JetBrains Mono'',monospace; font-size: 9,8pt;" /></p>', null);
INSERT INTO unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css) VALUES ('FORMATION_INSCRIPTION_LISTE_PRINCIPALE', '<p>Mail envoyer à l''étudiant lorsqu''il est inscrit en liste principale</p>', 'mail', 'Vous êtes sur la liste principale de la formation VAR[Formation#Libelle]', '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>Vous êtes inscrit·e en <strong>liste principale</strong> de la session de formation VAR[Formation#Libelle].<br />Celle-ci se déroulera selon les dates suivantes :<br />VAR[Session#SeancesTable]<br />Pensez à bien réserver cette date dans votre agenda.</p>
<p>Si vous avez besoin d''une convocation, vous pouvez retrouver celle-ci dans ESUP-SyGAL dans la partie ''<em>Mes formations</em>'' onglet ''<em>Mes inscriptions en cours</em>''.</p>
<p>En cas d’empêchement pensez à vous désinscrire afin de libérer votre place pour une personne de la liste complémentaire.</p>
<p>Cordialement,<br />VAR[Formation#Responsable]<br /><br /></p>', null);
INSERT INTO unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css) VALUES ('FORMATION_INSCRIPTION_LISTE_COMPLEMENTAIRE', null, 'mail', 'Vous êtes sur la liste complémentaire de la formation VAR[Formation#Libelle]', '<p>Bonjour VAR[Doctorant#Denomination],<br /><br />Vous êtes inscrit·e en <strong>liste complémentaire</strong> de la session de formation VAR[Formation#Libelle].<br />Celle-ci se déroulera selon les dates suivantes :<br />VAR[Session#SeancesTable]<br />Si une place en liste principale se libère vous serez informé·e par l''application ESUP-SyGAL.<br /><br />Cordialement,<br />VAR[Formation#Responsable]</p>', null);
INSERT INTO unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css) VALUES ('FORMATION_INSCRIPTION_ECHEC', '<p>Courrier électronique envoyé aux étudiant·e·s de la liste complémentaire lorsque la formation ne peut plus recevoir d''inscription sur la liste principale</p>', 'mail', 'La formation VAR[Formation#Libelle] est complète', '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>Vous étiez inscrit·e sur liste complémentaire à la formation VAR[Formation#Libelle] se déroulant :<br />VAR[Session#SeancesTable]</p>
<p>Cependant, aucune place ne s’étant libérée, nous sommes au regret de vous informer que vous ne pourrez pas participer à la formation.</p>
<p><br />Cordialement,<br />VAR[Formation#Responsable]</p>', null);
INSERT INTO unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css) VALUES ('FORMATION_SESSION_IMMINENTE', null, 'mail', 'La session de formation VAR[Formation#Libelle] va bientôt débutée', '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p><br />Nous vous rappelons que la formation VAR[Formation#Libelle]  à laquelle vous êtes inscrit·e va bientôt débuter.</p>
<p>Les séances de cette formation se tiendront :<br />VAR[Session#SeancesTable]</p>
<p><br />Cordialement,<br />VAR[Formation#Responsable]</p>', null);
INSERT INTO unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css) VALUES ('FORMATION_SESSION_TERMINEE', null, 'mail', 'La session de formation VAR[Formation#Libelle] est maintenant terminée.', '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>Nous espérons que la formation s’est bien déroulée.<br />Pour l’obtention de l’attestation de VAR[Session#Durée] heures de formation , il est nécessairement de remplir le questionnaire de satisfaction sur ESUP-SyGAL.</p>
<p>Cordialement,<br />VAR[Formation#Responsable]</p>', null);
INSERT INTO unicaen_renderer_template (code, description, document_type, document_sujet, document_corps, document_css) VALUES ('FORMATION_SESSION_ANNULEE', '<p>Courrier électronique envoyé aux inscrits des listes principale et complémentaire</p>', 'mail', 'La session de formation VAR[Formation#Libelle] vient d''être annulée', '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>La session de formation VAR[Formation#Libelle] vient d''être annulée.</p>
<p>Cordialement,<br />VAR[Formation#Responsable]</p>', null);
