-- Nouvelle nature de fichier pour l'attestation de responsabilité civile du doctorant
INSERT INTO nature_fichier (id, code, libelle)
VALUES (nextval('nature_fichier_id_seq'), 'ADMISSION_ATTESTATION_RESPONSABILITE_CIVILE', 'Attestation de responsabilité civile du doctorant');

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
<p>VAR[Admission#InfosDiplome]</p>
<h2>Inscription </h2>
<h3>Informations concernant son inscription demandée</h3>
<ul>
<li><strong>Discipline d''inscription : </strong>VAR[AdmissionInscription#DisciplineInscription]</li>
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
<p>VAR[Admission#Operations]</p>
<h2>Validation par la direction de l''établissement</h2>
<table style="border: white; border-collapse: collapse;" border="1">
<tbody>
<tr style="height: 35.6719px;">
<td style="width: 20%; border-color: white;">☐ Favorable</td>
<td style="width: 77.8498%; border-color: white;"> </td>
</tr>
<tr style="height: 35.6719px;">
<td style="width: 20%; border-color: white;">☐ Défavorable</td>
<td style="width: 77.8498%; border-color: white;">Motif du refus :</td>
</tr>
</tbody>
</table>
<p> </p>
<p> </p>
<p>Fait à ____________________, le ________________,</p>
<p>Signature de VAR[String#ToString]</p>'
WHERE code = 'ADMISSION_RECAPITULATIF';


--
-- Nouveaux privilèges.
--
insert into unicaen_privilege_privilege(ID, CATEGORIE_ID, CODE, LIBELLE, ORDRE)
with d(ordre, code, lib) as (select 1,
                                    'admission-configurer-module',
                                    'Configurer le module Admission'
)
select nextval('privilege_id_seq'), cp.id, d.code, d.lib, d.ordre
from d
         join unicaen_privilege_categorie cp on cp.CODE = 'admission'
WHERE NOT EXISTS (SELECT 1
                  FROM unicaen_privilege_privilege p
                  WHERE p.CODE = d.code);

-- ajout des privilèges au profil BDD (Maison du Doctorat)
INSERT INTO PROFIL_PRIVILEGE (PRIVILEGE_ID, PROFIL_ID)
with data(categ, priv) as (select 'admission', 'admission-configurer-module')
select p.id as PRIVILEGE_ID, profil.id as PROFIL_ID
from data
         join PROFIL on profil.ROLE_ID in (
    'ADMIN_TECH',
        'BDD' -- Maison du Doctorat
    )
         join unicaen_privilege_categorie cp on cp.CODE = data.categ
         join unicaen_privilege_privilege p on p.CATEGORIE_ID = cp.id and p.code = data.priv
where not exists (select * from PROFIL_PRIVILEGE where PRIVILEGE_ID = p.id and PROFIL_ID = profil.id);

insert into ROLE_PRIVILEGE (ROLE_ID, PRIVILEGE_ID)
select p2r.ROLE_ID, pp.PRIVILEGE_ID
from PROFIL_TO_ROLE p2r
         join profil pr on pr.id = p2r.PROFIL_ID
         join PROFIL_PRIVILEGE pp on pp.PROFIL_ID = pr.id
where not exists (select * from role_privilege where role_id = p2r.role_id and privilege_id = pp.privilege_id)
;

--Ajout de la variable UTILISATION_MODULE_ADMISSION à true pour les établissements d'inscription existants
INSERT INTO variable (id, etablissement_id, description, valeur, source_code, source_id, date_deb_validite, date_fin_validite, code, histo_createur_id)
SELECT
    nextval('variable_id_seq'),
    e.id AS etablissement_id,
    'Utilisation ou non du module Admission' AS description,
    'true' AS valeur,
    CONCAT(s.sigle, '::UTILISATION_MODULE_ADMISSION') AS source_code,
    1 AS source_id,
    NOW() AS date_deb_validite,
    NOW() + INTERVAL '10 years' AS date_fin_validite,
    'UTILISATION_MODULE_ADMISSION' AS code,
    1
FROM etablissement e
    JOIN structure s ON s.id = e.structure_id  -- Jointure avec la table structure
WHERE e.est_etab_inscription = true;