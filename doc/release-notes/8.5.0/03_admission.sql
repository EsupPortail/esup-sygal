INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionFinancement#EtablissementPartenaire',
        '<p>Retourne les détails de l''établissement partenaire dans le cas d''une convention de collaboration</p>', 'admissionFinancement',
        'getEtablissementPartenaire');

INSERT INTO unicaen_renderer_macro (code, description, variable_name, methode_name)
VALUES ('AdmissionConventionFormationDoctorale#InfosConfidentialite',
        '<p>Retourne les détails concernant la confidentialité souhaitée par l''étudiant</p>', 'admissionConventionFormationDoctoraleData',
        'getConfidentialiteInformationstoHtml');

UPDATE public.unicaen_renderer_template
SET document_corps = '<h1 style="text-align: center;">Récapitulatif du dossier d''admission</h1>
<h1 style="text-align: center;">de VAR[Individu#Denomination]</h1>
<h2>Étudiant</h2>
<h3>Informations concernant l''étudiant</h3>
<table style="height: 125px; width: 929px;">
<tbody>
<tr>
<td style="width: 163.5px;"><strong>Numéro I.N.E :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#INE]</td>
<td style="width: 145px;"><strong> </strong></td>
<td style="width: 303px;"> </td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Étudiant :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#DenominationEtudiant]</td>
<td style="width: 145px;"><strong>Date de naissance :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#DateNaissance]</td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Ville de naissance :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#VilleNaissance]</td>
<td style="width: 145px;"><strong>Pays de naissance :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#PaysNaissance]</td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Nationalité :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#Nationalite]</td>
<td style="width: 145px;"><strong>Adresse :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#Adresse]</td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Code postal :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#CodePostal]</td>
<td style="width: 145px;"><strong>Ville :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#VilleEtudiant]</td>
</tr>
<tr>
<td style="width: 163.5px;"><strong>Numéro de téléphone :</strong></td>
<td style="width: 285.5px;">VAR[AdmissionEtudiant#NumeroTelephone]</td>
<td style="width: 145px;"><strong>Mail :</strong></td>
<td style="width: 303px;">VAR[AdmissionEtudiant#MailEtudiant]</td>
</tr>
</tbody>
</table>
<p>Etes-vous en situation de handicap ? VAR[AdmissionEtudiant#SituationHandicap]</p>
<h3>Niveau permettant l''accès au doctorat</h3>
<p>VAR[AdmissionEtudiant#NiveauEtude] </p>
<p>VAR[AdmissionRecapitulatif#InfosDiplome]</p>
<h2>Inscription </h2>
<h3>Informations concernant son inscription demandée</h3>
<ul>
<li><strong>Spécialité d''inscription :</strong> VAR[AdmissionInscription#SpecialiteDoctorat]</li>
<li><strong>Composante de rattachement : </strong>VAR[AdmissionInscription#ComposanteRattachement]</li>
<li><strong>École Doctorale :</strong> VAR[AdmissionInscription#EcoleDoctorale]</li>
<li><strong>Unité de recherche :</strong> VAR[AdmissionInscription#UniteRecherche]</li>
<li><strong>Établissement hébergeant le laboratoire de recherche</strong> :  VAR[AdmissionInscription#EtablissementLaboratoireRecherche]</li>
<li><strong>Établissement d''inscription</strong> : VAR[AdmissionInscription#EtablissementInscription] </li>
<li><strong>Directeur(-trice) de thèse :</strong> VAR[AdmissionInscription#DenominationDirecteurThese]
<ul>
<li><strong>Fonction du directeur(-trice) de thèse :</strong> VAR[AdmissionInscription#FonctionDirecteurThese]</li>
</ul>
</li>
<li><strong>Titre provisoire de la thèse :</strong> VAR[AdmissionInscription#TitreThese]</li>
</ul>
<h3>Spéciﬁcités envisagées concernant son inscription</h3>
<ul>
<li><strong>Conﬁdentialité souhaitée :</strong> VAR[AdmissionInscription#ConfidentialiteSouhaitee]</li>
<li><strong>Cotutelle envisagée :</strong> VAR[AdmissionInscription#CotutelleEnvisagee]</li>
<li><strong>Codirection demandée :</strong> VAR[AdmissionInscription#CoDirectionDemandee]
<ul>
<li><strong>Si oui :</strong>
<ul>
<li><strong>fonction du codirecteur(-trice) de thèse :</strong> VAR[AdmissionInscription#FonctionCoDirecteurThese]</li>
<li><strong>unité de recherche</strong> : VAR[AdmissionInscription#UniteRechercheCoDirection]</li>
<li><strong>établissement de rattachement</strong> : VAR[AdmissionInscription#EtablissementRattachementCoDirection]</li>
</ul>
</li>
</ul>
</li>
<li><strong>Co-encadrement envisagé :</strong> VAR[AdmissionInscription#CoEncadrementEnvisage]</li>
</ul>
<h2>Financement</h2>
<p><strong>Avez-vous un contrat doctoral</strong> <strong>?</strong> VAR[AdmissionFinancement#ContratDoctoral]</p>
<ul>
<li><strong>Si oui, détails du contrat doctoral</strong> : VAR[AdmissionFinancement#DetailContratDoctoral]</li>
</ul>
<p><strong>Temps de travail du Doctorat mené à</strong> : VAR[AdmissionFinancement#TempsTravail]</p>
<p><strong>Êtes-vous salarié ?</strong> VAR[AdmissionFinancement#EstSalarie]</p>
<ul>
<li><strong>Si oui :</strong>
<ul>
<li><strong>statut professionnel </strong>: VAR[AdmissionFinancement#StatutProfessionnel]</li>
<li><strong>dans le cadre d''une convention de collaboration, informations concernant l''établissement partenaire</strong> : VAR[AdmissionFinancement#EtablissementPartenaire]</li>
</ul>
</li>
</ul>
<h2>Validations et Avis accordés au dossier d''admission</h2>
<p>VAR[AdmissionRecapitulatif#Operations]</p>
<h2>Validation par la direction de l''établissement</h2>
<ul>
<li>Favorable</li>
<li>Défavorable
<ul>
<li>Motif du refus :</li>
</ul>
</li>
</ul>
<p> </p>
<p> </p>
<p>Fait à ____________________, le ________________,</p>
<p>Signature de VAR[String#ToString]</p>'
WHERE code = 'ADMISSION_RECAPITULATIF';

UPDATE public.unicaen_renderer_template
SET document_corps = '<h1 style="text-align: center;">Convention de formation doctorale</h1>
<h1 style="text-align: center;">de VAR[Individu#Denomination]</h1>
<p> </p>
<p><strong>Entre</strong> VAR[AdmissionEtudiant#DenominationEtudiant]<br /><strong>né le</strong> VAR[AdmissionEtudiant#DateNaissance]<br /><strong>à</strong> VAR[AdmissionEtudiant#VilleNaissance], VAR[AdmissionEtudiant#PaysNaissance] (VAR[AdmissionEtudiant#Nationalite])<br /><strong>ayant pour adresse</strong> VAR[AdmissionEtudiant#Adresse], VAR[AdmissionEtudiant#CodePostal] VAR[AdmissionEtudiant#VilleEtudiant], VAR[AdmissionEtudiant#PaysEtudiant]  </p>
<p><strong>Mail</strong> : VAR[AdmissionEtudiant#MailEtudiant]</p>
<p><strong>ci-après dénommé le Doctorant</strong></p>
<p><strong>Et</strong> VAR[AdmissionInscription#DenominationDirecteurThese], directeur(-trice) de thèse<br /><strong>Fonction</strong> : VAR[AdmissionInscription#FonctionDirecteurThese]<br /><strong>Unité de recherche </strong>: VAR[AdmissionInscription#UniteRecherche]<br /><strong>Établissement de rattachement</strong> : VAR[AdmissionInscription#EtablissementInscription]<br /><strong>Mail</strong> : VAR[AdmissionInscription#MailDirecteurThese]</p>
<p>VAR[AdmissionConventionFormationDoctorale#InfosCoDirecteur]</p>
<p>- Vu l’article L612-7 du Code de l’éducation, Vu les articles L412-1 et L412-2 du Code de la recherche,</p>
<p>- Vu l’arrêté du 25 mai 2016 fixant le cadre national de la formation et les modalités conduisant à la délivrance du diplôme national de doctorat, modifié par l’arrêté du 26 août 2022,</p>
<p>- Vu la charte du doctorat dans le cadre de la délivrance conjointe du doctorat entre la ComUE Normandie Université et les établissements d’inscription co-accrédités en date du 14 mai 2024,  VAR[AdmissionConventionFormationDoctorale#InfosCoTutelle]
<p>VAR[AdmissionConventionFormationDoctorale#InfosConventionCollaboration]</p>
<p>Il est établi la convention de formation doctorale suivante. Cette convention peut être modifiée par avenant autant de fois que nécessaire pendant le doctorat.</p>
<h3><strong>Article 1 - Objet de la convention de formation doctorale</strong></h3>
<p>La présente convention de formation, signée à la première inscription par le Doctorant, par le (ou les) (co)Directeur(s) de Thèse et, le cas échéant, par le(s) co-encadrant(s) de thèse, fixe les conditions de suivi et d''encadrement de la thèse, sous la responsabilité de l’VAR[AdmissionInscription#EtablissementInscription] (établissement d’inscription), établissement de préparation du doctorat. En cas de besoin, la convention de formation doctorale est révisable annuellement par un avenant à la convention initiale.</p>
<p>Les règles générales en matière de signature des travaux issus de la thèse, de confidentialité et de propriété intellectuelle sont précisées dans la Charte du doctorat adoptée par Normandie Université et l’VAR[AdmissionInscription#EtablissementInscription] (établissement d’inscription), également signée à la première inscription, par le Doctorant et le (ou les) Directeur(s) de Thèse.</p>
<p><strong>Titre de la thèse </strong></p>
<p>VAR[AdmissionInscription#TitreThese]</p>
<p><strong>Spécialité du diplôme</strong></p>
<p>VAR[AdmissionInscription#SpecialiteDoctorat]</p>
<p><strong>Financement</strong></p>
<p>VAR[AdmissionFinancement#ContratDoctoral]</p>
<p><strong>Établissement d''inscription du doctorant</strong></p>
<p>VAR[AdmissionInscription#EtablissementInscription]</p>
<p><strong>École doctorale</strong></p>
<p>VAR[AdmissionInscription#EcoleDoctorale]</p>
<p><strong>Temps de travail du Doctorat mené à</strong> : VAR[AdmissionFinancement#TempsTravail]</p>
<p><strong>Statut professionnel </strong>: VAR[AdmissionFinancement#StatutProfessionnel]</p>
<h3><strong>Article 2 – Laboratoire d’accueil</strong></h3>
<p>Le doctorant réalise sa thèse au sein de :</p>
<ul>
<li style="font-weight: 400; text-align: start;">Unité d''accueil : VAR[AdmissionInscription#UniteRecherche]</li>
<li style="font-weight: 400; text-align: start;">Directeur de l''unité : VAR[AdmissionConventionFormationDoctorale#ResponsablesURDirecteurThese]</li>
</ul>
<p>VAR[AdmissionConventionFormationDoctorale#InfosCoTutelleCoDirection]</p>
<h3><strong>Article 3 - Méthodes et Moyens</strong></h3>
<p><strong>3-1 Calendrier prévisionnel du projet de recherche</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#CalendrierPrevisionnel]</p>
<p><strong>3-2 Modalités d''encadrement, de suivi de la formation et d''avancement des recherches du doctorant</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ModalitesEncadrement]</p>
<p><strong>3-3 Conditions matérielles de réalisation du projet de recherche et conditions de sécurité spécifiques si nécessaire</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ConditionsProjRech]</p>
<p><strong>3-4 Modalités d''intégration dans l''unité ou l’équipe de recherche</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ModalitesIntegrationUR]</p>
<p><strong>3-5 Partenariats impliqués par le projet de thèse</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#PartenariatsProjThese]</p>
<h3><strong>Article 4 - Confidentialité des travaux de recherche</strong></h3>
<p>Caractère confidentiel des travaux : VAR[AdmissionConventionFormationDoctorale#InfosConfidentialite]</p>
<p><strong>Article 5 - Projet professionnel du doctorant</strong></p>
<p>VAR[AdmissionConventionFormationDoctorale#ProjetProDoctorant]</p>
<p> </p>
<h2>Validations accordées à la convention de formation doctorale</h2>
<p>VAR[AdmissionConventionFormationDoctorale#Operations]</p>'
WHERE code = 'ADMISSION_CONVENTION_FORMATION_DOCTORALE';