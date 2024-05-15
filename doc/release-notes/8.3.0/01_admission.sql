--Mise à jour du sujet et du corps de certains templates (associés au module Admission)
UPDATE public.unicaen_renderer_template SET document_sujet = 'Avis ajouté au dossier d''admission de VAR[Individu#Denomination]', document_corps = e'<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>Un avis a été ajouté au <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> par VAR[AdmissionAvis#Auteur], le VAR[AdmissionAvis#Date]</p>
<p>Afin de suivre l''avancée du dossier, connectez-vous sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission]</p>' WHERE code = 'ADMISSION_AVIS_AJOUTE';
UPDATE public.unicaen_renderer_template SET document_sujet = 'Avis modifié sur le dossier d''admission de VAR[Individu#Denomination]', document_corps = '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>L''<strong>VAR[TypeValidation#Libelle]</strong> du <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> datant du VAR[AdmissionAvis#Date] a été modifié<strong> </strong>VAR[AdmissionAvis#Modificateur]</p>
<p>Afin de suivre l''avancée du dossier, connectez-vous sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission]</p>' WHERE code = 'ADMISSION_AVIS_MODIFIE';
UPDATE public.unicaen_renderer_template SET document_sujet = 'Avis supprimé sur le dossier d''admission de VAR[Individu#Denomination]', document_corps = '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>L''<strong>VAR[TypeValidation#Libelle]</strong> du <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> datant du VAR[AdmissionAvis#Date] a été <strong>supprimé </strong>VAR[AdmissionAvis#Destructeur]</p>
<p>Afin de suivre l''avancée du dossier, connectez-vous sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission]</p>' WHERE code = 'ADMISSION_AVIS_SUPPRIME';
UPDATE public.unicaen_renderer_template SET document_sujet = 'Commentaires ajoutés sur le dossier d''admission de VAR[Individu#Denomination]' WHERE code = 'ADMISSION_COMMENTAIRES_AJOUTES';
UPDATE public.unicaen_renderer_template SET document_sujet = 'Dossier d''admission de VAR[Individu#Denomination] validé' WHERE code = 'ADMISSION_DERNIERE_VALIDATION_AJOUTEE';
UPDATE public.unicaen_renderer_template SET document_sujet = 'Dossier d''admission de VAR[Individu#Denomination] incomplet', document_corps = '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>Le <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> a été déclaré comme <strong>incomplet</strong> par VAR[AdmissionAvis#Auteur], le VAR[AdmissionAvis#Date].</p>
<p>Cela a pour conséquence de <strong>supprimer l''intégralité des validations préalablement effectuées</strong>.</p>
<p>Veuillez prendre connaissance de cette déclaration, en vous connectant sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission] </p>' WHERE code = 'ADMISSION_NOTIFICATION_DECLARATION_DOSSIER_INCOMPLET';
UPDATE public.unicaen_renderer_template SET document_sujet = 'Dossier d''admission de VAR[Individu#Denomination] incomplet' WHERE code = 'ADMISSION_NOTIFICATION_DOSSIER_INCOMPLET';
UPDATE public.unicaen_renderer_template SET document_sujet = 'Opération attendue sur le dossier d''admission de VAR[Individu#Denomination]' WHERE code = 'ADMISSION_OPERATION_ATTENDUE';
UPDATE public.unicaen_renderer_template SET document_sujet = 'Validation ajoutée au dossier d''admission de VAR[Individu#Denomination]', document_corps = '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>Le <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> a été <strong>validé</strong> par VAR[AdmissionValidation#Auteur], le VAR[AdmissionValidation#Date]</p>
<p>Afin de suivre l''avancée du dossier, connectez-vous sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission]</p>' WHERE code = 'ADMISSION_VALIDATION_AJOUTEE';
UPDATE public.unicaen_renderer_template SET document_sujet = 'Validation supprimée au dossier d''admission de VAR[Individu#Denomination]', document_corps = '<p>Bonjour,</p>
<p>Ceci est un mail envoyé automatiquement par l''application ESUP-SyGAL.</p>
<p>La <strong>VAR[TypeValidation#Libelle]</strong> du <strong>dossier d''admission</strong> de <strong>VAR[Individu#Denomination]</strong> datant du VAR[AdmissionValidation#Date] a été <strong>annulée </strong>VAR[AdmissionValidation#Destructeur]</p>
<p>Afin de suivre l''avancée du dossier, connectez-vous sur la plateforme ESUP-SyGAL via le lien suivant : VAR[Url#Admission]</p>' WHERE code = 'ADMISSION_VALIDATION_SUPPRIMEE';

--Suppression de certains templates (associés au module Admission)
DELETE FROM public.unicaen_renderer_template where code = 'ADMISSION_NOTIFICATION_DOSSIER_COMPLET';
DELETE FROM public.unicaen_renderer_template where code = 'ADMISSION_NOTIFICATION_GESTIONNAIRE';