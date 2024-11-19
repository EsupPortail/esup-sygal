--Ajout d'une nouvelle macro pour ne pas afficher le lien/lieu d'une session de formation dans certains templates
INSERT INTO public.unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('Session#SeancesTableSansLieu', '<p>Retourne la liste des séances avec le lieu sous la forme d''un tableau HTML</p>', 'session',
        'getSeancesSansLieuAsTable');

--Mise à jour en conséquence de certains templates
UPDATE public.unicaen_renderer_template
SET document_corps = '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>Nous avons bien reçu votre demande d’inscription à la formation VAR[Formation#Libelle] se déroulant : <br />VAR[Session#SeancesTableSansLieu]</p>
<p>Vous recevrez une validation de suivi de la formation à J-7 au plus tard.</p>
<p><br />Cordialement,<br />VAR[Formation#Responsable]<br /><br style="background-color: #2b2b2b; color: #a9b7c6; font-family: ''JetBrains Mono'',monospace; font-size: 9,8pt;" /></p>'
WHERE code = 'FORMATION_INSCRIPTION_ENREGISTREE';

UPDATE public.unicaen_renderer_template
SET document_corps = '<p>Bonjour VAR[Doctorant#Denomination],<br /><br />Vous êtes inscrit·e en <strong>liste complémentaire</strong> de la session de formation VAR[Formation#Libelle].<br />Vous êtes à la position VAR[Inscription#PositionComplementaire] sur cette liste.</p>
<p><br />La session de formation se déroulera selon les dates suivantes :<br />VAR[Session#SeancesTableSansLieu]<br />Si une place en liste principale se libère vous serez informé·e par l''application ESUP-SyGAL.<br /><br />Cordialement,<br />VAR[Formation#Responsable]</p>'
WHERE code = 'FORMATION_INSCRIPTION_LISTE_COMPLEMENTAIRE';

UPDATE public.unicaen_renderer_template
SET document_corps = '<p>Bonjour VAR[Doctorant#Denomination],</p>
<p>Vous étiez inscrit sur liste complémentaire à la formation VAR[Formation#Libelle] se déroulant :<br />VAR[Session#SeancesTableSansLieu]</p>
<p><strong>Cependant, aucune place ne s’étant libérée, nous sommes au regret de vous informer que vous ne pourrez pas participer à la formation.</strong></p>
<p><br />Cordialement,</p>
<p>VAR[Session#Responsable]</p>'
WHERE code = 'FORMATION_INSCRIPTION_ECHEC';