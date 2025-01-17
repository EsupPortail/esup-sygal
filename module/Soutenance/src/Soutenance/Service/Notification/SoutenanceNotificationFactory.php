<?php

namespace Soutenance\Service\Notification;

use Application\Entity\Db\Role;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Validation;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManagerAwareTrait;
use Application\Service\Email\EmailTheseServiceAwareTrait;
use Application\Service\Role\ApplicationRoleServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Doctorant\Entity\Db\Doctorant;
use Individu\Entity\Db\Individu;
use Notification\Exception\RuntimeException;
use Notification\Factory\NotificationFactory;
use Notification\Notification;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Provider\Template\MailTemplates;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use Soutenance\Service\Url\UrlServiceAwareTrait;
use These\Entity\Db\These;
use These\Service\Acteur\ActeurServiceAwareTrait;
use These\Service\These\TheseServiceAwareTrait;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

/** Todo à déplacer dans UnicaenRenderer dans les prochaines versions */
class StringElement
{
    public string $texte = "Aucun texte";

    public function getTexte(): string
    {
        return $this->texte;
    }
}

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class SoutenanceNotificationFactory extends NotificationFactory
{
    use ActeurServiceAwareTrait;
    use EmailTheseServiceAwareTrait;
    use MembreServiceAwareTrait;
    use ApplicationRoleServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EmailTheseServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use RenduServiceAwareTrait;
    use UrlServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use TemplateVariablePluginManagerAwareTrait;

    public function createNotificationDevalidationProposition(These $these, Validation $validation): Notification
    {
        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $validationTemplateVariable = $this->getValidationTemplateVariable($validation);
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'validation' => $validationTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_VALIDATION_ANNULEE, $vars);
        $mail = $validation->getIndividu()->getEmailUtilisateur();
        if ($mail === null) {
            throw new RuntimeException("Aucun mail trouvé pour " . $validation->getIndividu()->getNomComplet());
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationValidationProposition(These $these, Validation $validation): Notification
    {
        $emails = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });
        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour les acteurs directs de la thèse {$these->getId()}");
        }

        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $validationTemplateVariable = $this->getValidationTemplateVariable($validation);
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'validation' => $validationTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_VALIDATION_ACTEUR_DIRECT, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationUniteRechercheProposition(These $these): Notification
    {
        $individuRoles = $this->applicationRoleService->findIndividuRoleByStructure($these->getUniteRecherche()->getStructure(), null, $these->getEtablissement());
        //
        // todo : quid si rien pour l'établissement spécifié ? => il faut bien pouvoir notifier qqun, non ?!
        // if (!$individuRoles) {
        //     // tentative sans contrainte sur l'établissement
        //     $individuRoles = $this->roleService->findIndividuRoleByStructure($these->getUniteRecherche()->getStructure());
        // }
        //
        $emails = $this->emailTheseService->collectEmailsFromIndividuRoles($individuRoles);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour l'unité de recherche de la thèse {$these->getId()}");
        }

        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $uniteRechercheTemplateVariable = $this->getStructureTemplateVariable($these->getUniteRecherche());
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'unite-recherche' => $uniteRechercheTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_VALIDATION_DEMANDE_UR, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationEcoleDoctoraleProposition(These $these): Notification
    {
        $individuRoles = $this->applicationRoleService->findIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure(), null, $these->getEtablissement());
        $emails = $this->emailTheseService->collectEmailsFromIndividuRoles($individuRoles);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour l'école doctorale de la thèse {$these->getId()}");
        }

        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($these->getEtablissement());
        $uniteRechercheTemplateVariable = $this->getStructureTemplateVariable($these->getUniteRecherche());
        $ecoleDoctoraleTemplateVariable = $this->getStructureTemplateVariable($these->getEcoleDoctorale());
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'ecole-doctorale' => $ecoleDoctoraleTemplateVariable,
            'etablissement' => $etablissementTemplateVariable,
            'unite-recherche' => $uniteRechercheTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_VALIDATION_DEMANDE_ED, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationBureauDesDoctoratsProposition(These $these): Notification
    {
        $emails = $this->emailTheseService->fetchEmailAspectsDoctorat($these);

        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($these->getEtablissement());
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'etablissement' => $etablissementTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_VALIDATION_DEMANDE_ETAB, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationPropositionValidee(These $these): Notification
    {
        $emailsBDD = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emailsActeurs = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emails = array_merge($emailsBDD, $emailsED, $emailsUR, $emailsActeurs);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse électronique trouvée pour la thèse {$these->getId()}");
        }

        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($these->getEtablissement());
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'etablissement' => $etablissementTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::VALIDATION_SOUTENANCE_AVANT_PRESOUTENANCE, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationSuppressionProposition(These $these): Notification
    {
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emailsActeurs = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emails = array_merge(
            $emailsED, $emailsUR, $emailsActeurs);
        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });
        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour les acteurs directs de la thèse {$these->getId()}");
        }

        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::PROPOSITION_SUPPRESSION, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationPresoutenance(These $these): Notification
    {
        $emails = $this->emailTheseService->fetchEmailAspectsDoctorat($these);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la maison du doctorat de la thèse {$these->getId()}");
        }

        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($these->getEtablissement());
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'etablissement' => $etablissementTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::VALIDATION_SOUTENANCE_ENVOI_PRESOUTENANCE, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationRefusPropositionSoutenance(These $these, Individu $currentUser, Role $currentRole, string $motif): Notification
    {
        $emails = $this->emailTheseService->fetchEmailActeursDirects($these);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse électronique trouvée pour les acteurs directs de la thèse {$these->getId()}");
        }

        $refus = new StringElement();
        $refus->texte = $motif;

        $this->urlService->setVariables([
            'these' => $these,
        ]);
        
        $individuTemplateVariable = $this->getIndividuTemplateVariable($currentUser);
        $roleTemplateVariable = $this->getRoleTemplateVariable($currentRole);
        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($these->getEtablissement());
        $vars = [
            'individu' => $individuTemplateVariable,
            'role' => $roleTemplateVariable,
            'etablissement' => $etablissementTemplateVariable,
            'stringelement' => $refus,
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::PROPOSITION_REFUS, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    /** ENGAGEMENT IMPARTIALITE ***************************************************************************************/

    public function createNotificationDemandeSignatureEngagementImpartialite(These $these, Membre $membre): Notification
    {
        $this->urlService->setVariables([
            'these' => $these,
            'rapporteur' => $membre,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $soutenanceMembre = $this->getSoutenanceMembreTemplateVariable($membre);
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'soutenanceMembre' => $soutenanceMembre,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DEMANDE_ENGAGEMENT_IMPARTIALITE, $vars);
        $mail = $membre->getActeur()?->getEmail(true);
        if ($mail === null) {
            throw new RuntimeException("Aucun mail trouvé pour le rapporteur");
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationSignatureEngagementImpartialite(These $these, Membre $membre): Notification
    {
        $this->urlService->setVariables([
            'these' => $these,
            'membre' => $membre,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $soutenanceMembre = $this->getSoutenanceMembreTemplateVariable($membre);
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'soutenanceMembre' => $soutenanceMembre,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SIGNATURE_ENGAGEMENT_IMPARTIALITE, $vars);
        $email = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
        if (empty($email)) {
            throw new RuntimeException("Aucun mail trouvé pour la maison du doctorat de " . $these->getEtablissement()->getStructure()->getLibelle());
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationRefusEngagementImpartialite(These $these, Membre $membre): Notification
    {
        $this->urlService->setVariables([
            'these' => $these,
            'membre' => $membre,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $soutenanceMembre = $this->getSoutenanceMembreTemplateVariable($membre);
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'soutenanceMembre' => $soutenanceMembre,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::REFUS_ENGAGEMENT_IMPARTIALITE, $vars);

        $emailsAD = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emailsBDD = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
        $emails = array_merge($emailsAD, $emailsBDD);
        if (empty($emails)) {
            throw new RuntimeException("Aucun mail trouvé");
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationAnnulationEngagementImpartialite(These $these, Membre $membre): Notification
    {
        $this->urlService->setVariables([
            'these' => $these,
            'membre' => $membre,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($membre);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'soutenanceMembre' => $soutenanceMembreTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::ANNULATION_ENGAGEMENT_IMPARTIALITE, $vars);

        $mail = $membre->getEmail();
        if ($mail === null) {
            throw new RuntimeException("Aucun mail trouvé pour le rapporteur");
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());
        return $notif;
    }


    /**************************** avis ***************************/

    public function createNotificationAvisRendus(These $these): Notification
    {
        $email = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
        if (empty($email)) {
            throw new RuntimeException("Aucune adresse électronique trouvée pour les aspects doctorales");
        }

        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_TOUS_AVIS_RENDUS, $vars);

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationAvisRendusDirection(These $these): Notification
    {
        $email = $this->emailTheseService->fetchEmailEncadrants($these);
        if (empty($email)) {
            throw new RuntimeException("Aucune adresse électronique trouvée pour les encadrants de la thèse");
        }

        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_TOUS_AVIS_RENDUS_DIRECTION, $vars);

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationAvisFavorable(These $these, Avis $avis): Notification
    {
        $emailBDD = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emailsDirecteurs = $this->emailTheseService->fetchEmailEncadrants($these);
        $emails = array_merge($emailBDD, $emailsDirecteurs, $emailsED, $emailsUR);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la notification [" . MailTemplates::SOUTENANCE_AVIS_FAVORABLE . "] la thèse {$these->getId()}");
        }

        $this->urlService->setVariables([
            'these' => $these,
            'membre' => $avis->getMembre(),
            'rapporteur' => $avis->getRapporteur(),
            'avis' => $avis,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($avis->getMembre());
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $acteurTemplateVariable = $this->getActeurTemplateVariable($avis->getRapporteur());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($these->getEtablissement());
        $uniteRechercheTemplateVariable = $this->getStructureTemplateVariable($these->getUniteRecherche());
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'soutenanceMembre' => $soutenanceMembreTemplateVariable,
            'acteur' => $acteurTemplateVariable,
//            'avis' => $avis, // enlevé car aucune macro utilisant une variable 'avis'
            'etablissement' => $etablissementTemplateVariable,
            'unite-recherche' => $uniteRechercheTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_AVIS_FAVORABLE, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationAvisDefavorable(These $these, Avis $avis): Notification
    {
        $emailBDD = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
        $emailsDirecteurs = $this->emailTheseService->fetchEmailEncadrants($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailBDD, $emailsDirecteurs, $emailsED, $emailsUR);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la notification [" . MailTemplates::SOUTENANCE_AVIS_DEFAVORABLE . "] la thèse {$these->getId()}");
        }

        $this->urlService->setVariables([
            'these' => $these,
            'membre' => $avis->getMembre(),
            'rapporteur' => $avis->getRapporteur(),
            'avis' => $avis,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($avis->getMembre());
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $acteurTemplateVariable = $this->getActeurTemplateVariable($avis->getRapporteur());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($these->getEtablissement());
        $uniteRechercheTemplateVariable = $this->getStructureTemplateVariable($these->getUniteRecherche());

        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'soutenanceMembre' => $soutenanceMembreTemplateVariable,
            'acteur' => $acteurTemplateVariable,
//            'avis' => $avis, // enlevé car aucune macro utilisant une variable 'avis'
            'etablissement' => $etablissementTemplateVariable,
            'unite-recherche' => $uniteRechercheTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_AVIS_DEFAVORABLE, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }


    /**************************** présoutenance ***************************/

    public function createNotificationDemandeAvisSoutenance(These $these, Membre $rapporteur): Notification
    {
        $email = $rapporteur->getActeur()?->getEmail(true);
        if ($email === null) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la notification [" . MailTemplates::DEMANDE_PRERAPPORT . "] la thèse {$these->getId()}");
        }

        $this->urlService->setVariables([
            'these' => $these,
            'membre' => $rapporteur,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($rapporteur);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());

        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'soutenanceMembre' => $soutenanceMembreTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DEMANDE_PRERAPPORT, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationFeuVertSoutenance(Proposition $proposition): Notification
    {
        $these = $proposition->getThese();

        $emailsActeurs = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailsActeurs, $emailsED, $emailsUR);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la notification [" . MailTemplates::SOUTENANCE_FEU_VERT . "] la thèse {$these->getId()}");
        }

        $this->urlService->setVariables([
            'soutenance' => $proposition,
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($these->getEtablissement());
        $uniteRechercheTemplateVariable = $this->getStructureTemplateVariable($these->getUniteRecherche());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        $vars = [
            'soutenance' => $soutenancePropositionTemplateVariable,
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'etablissement' => $etablissementTemplateVariable,
            'unite-recherche' => $uniteRechercheTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_FEU_VERT, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationStopperDemarcheSoutenance($these): Notification
    {
        $emailsActeurs = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailsActeurs, $emailsED, $emailsUR);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la notification [" . MailTemplates::SOUTENANCE_STOP_DEMARCHE . "] la thèse {$these->getId()}");
        }

        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $vars = [
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_STOP_DEMARCHE, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    /** Mail concernants les rapporteur·trices ************************************************************************/

    public function createNotificationConnexionRapporteur(Proposition $proposition, Membre $rapporteur): Notification
    {
        $mail = $rapporteur->getEmail();
        if ($mail === null) {
            throw new RuntimeException("Aucun mail trouvé pour le rapporteur " . $rapporteur->getDenomination() . " (id:" . $rapporteur->getId() . ")");
        }

        $these = $proposition->getThese();

        $this->urlService->setVariables([
            'soutenance' => $proposition,
            'these' => $these,
            'rapporteur' => $rapporteur,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($rapporteur);
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($these->getEtablissement());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        $vars = [
            'soutenanceProposition' => $soutenancePropositionTemplateVariable,
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'soutenanceMembre' => $soutenanceMembreTemplateVariable,
            'etablissement' => $etablissementTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::CONNEXION_RAPPORTEUR, $vars);

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationNotificationRapporteurRetard(Membre $membre): Notification
    {
        if ($membre->getActeur() === null) {
            throw new RuntimeException("Notification vers rapporteur [MembreId = " . $membre->getId() . "] impossible car aucun acteur n'est lié.");
        }
        $email = $membre->getEmail();
        if ($email === null) {
            throw new RuntimeException("Notification vers rapporteur [MembreId = " . $membre->getId() . "] impossible car aucun email est donné pour l'individu associé [IndividuId = " . $membre->getIndividu()->getId() . "].");
        }

        $proposition = $membre->getProposition();
        $these = $proposition->getThese();

        $this->urlService->setVariables([
            'soutenance' => $proposition,
            'these' => $these,
            'rapporteur' => $membre,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($membre);
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($these->getEtablissement());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        $vars = [
            'soutenanceProposition' => $soutenancePropositionTemplateVariable,
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'soutenanceMembre' => $soutenanceMembreTemplateVariable,
            'etablissement' => $etablissementTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DEMANDE_RAPPORT_SOUTENANCE, $vars);

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    /** Mails de fin de procédure *************************************************************************************/

    public function createNotificationEnvoiConvocationDoctorant(Doctorant $doctorant, Proposition $proposition): Notification
    {
        $email = $doctorant->getIndividu()->getEmailUtilisateur();
        if ($email === null) {
            throw new RuntimeException("Aucun mail pour la notification [" . MailTemplates::SOUTENANCE_CONVOCATION_DOCTORANT . "]");
        }
        $validation = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $proposition->getThese());
        if (empty($validation)) {
            throw new RuntimeException("Aucune validation de trouvée");
        }

        $this->urlService->setVariables([
            'soutenance' => $proposition,
            'these' => $proposition->getThese(),
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($proposition->getThese());
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($doctorant);
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        $validationTemplateVariable = $this->getValidationTemplateVariable($validation[0]);
        $vars = [
            'soutenanceProposition' => $soutenancePropositionTemplateVariable,
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'validation' => $validationTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_CONVOCATION_DOCTORANT, $vars);

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationEnvoiConvocationMembre(Membre $membre, Proposition $proposition): Notification
    {
        $email = $membre->getEmail();
        $doctorant = $proposition->getThese()->getDoctorant();
        if ($email === null) {
            throw new RuntimeException("Aucun mail pour la notification [" . MailTemplates::SOUTENANCE_CONVOCATION_DOCTORANT . "]");
        }
        $validation = $this->getValidationService()->getRepository()->findValidationByCodeAndThese(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD, $proposition->getThese());
        if (empty($validation)) {
            throw new RuntimeException("Aucune validation de trouvée");
        }

        $this->urlService->setVariables([
            'soutenance' => $proposition,
            'these' => $proposition->getThese(),
            'membre' => $membre,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($proposition->getThese());
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($doctorant);
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($proposition->getThese()->getEtablissement());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        $validationTemplateVariable = $this->getValidationTemplateVariable($validation[0]);
        $soutenanceMembreTemplateVariable = $this->getSoutenanceMembreTemplateVariable($membre);
        $soutenanceMembreTemplateVariable->setMembresPouvantEtrePresidentDuJury(
            $this->membreService->findAllMembresPouvantEtrePresidentDuJury($proposition)
        );
        $vars = [
            'soutenanceProposition' => $soutenancePropositionTemplateVariable,
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'soutenanceMembre' => $soutenanceMembreTemplateVariable,
            'validation' => $validationTemplateVariable,
            'etablissement' => $etablissementTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_CONVOCATION_MEMBRE, $vars);

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationTransmettreDocumentsDirectionThese(These $these, Proposition $proposition): Notification
    {
        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($proposition->getThese());
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $etablissementTemplateVariable = $this->getStructureTemplateVariable($these->getEtablissement());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        $vars = [
            'soutenanceProposition' => $soutenancePropositionTemplateVariable,
            'these' => $theseTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'etablissement' => $etablissementTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::TRANSMETTRE_DOCUMENTS_DIRECTION, $vars);
        $mail = array_merge($these->getDirecteursTheseEmails(), $these->getCoDirecteursTheseEmails());
        if (count($mail) === 0) {
            throw new RuntimeException("Aucun mail trouvés pour les directeurs de thèse");
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());

        return $notif;
    }

    public function createNotificationDemandeAdresse(?Proposition $proposition): Notification
    {
        $these = $proposition->getThese();

        $this->urlService->setVariables([
            'these' => $these,
        ]);

        $theseTemplateVariable = $this->getTheseTemplateVariable($these);
        $doctorantTemplateVariable = $this->getDoctorantTemplateVariable($these->getDoctorant());
        $soutenancePropositionTemplateVariable = $this->getSoutenancePropositionTemplateVariable($proposition);
        $vars = [
            'these' => $theseTemplateVariable,
            'soutenanceProposition' => $soutenancePropositionTemplateVariable,
            'doctorant' => $doctorantTemplateVariable,
            'Url' => $this->urlService,
        ];

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DEMANDE_ADRESSE_EXACTE, $vars);
        $emails = $this->emailTheseService->fetchEmailActeursDirects($these);
        if (count($emails) === 0) {
            throw new RuntimeException("Aucun mail trouvés pour les acteurs directs");
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());

        return $notif;
    }
}