<?php

namespace Soutenance\Service\Notification;

use Application\Entity\Db\Role;
use Application\Entity\Db\Validation;
use Application\Service\Email\EmailTheseServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use DateTime;
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

class StringElement {
    public string $texte = "Aucun texte";
    public function getTexte() : string {return $this->texte; }
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
    use RoleServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EmailTheseServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use RenduServiceAwareTrait;
    use UrlServiceAwareTrait;

    public function createNotificationDevalidationProposition(These $these, Validation $validation): Notification
    {
        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'validation' => $validation];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'validation' => $validation];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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
        $individuRoles = $this->roleService->findIndividuRoleByStructure($these->getUniteRecherche()->getStructure());
        $emails = $this->emailTheseService->fetchEmailsByEtablissement($individuRoles, $these);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour l'unité de recherche de la thèse {$these->getId()}");
        }

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'unite-recherche' => $these->getUniteRecherche()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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
        $individuRoles = $this->roleService->findIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure());
        $emails = $this->emailTheseService->fetchEmailsByEtablissement($individuRoles, $these);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour l'école doctorale de la thèse {$these->getId()}");
        }

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'ecole-doctorale' => $these->getEcoleDoctorale()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'etablissement' => $these->getEtablissement()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'etablissement' => $these->getEtablissement()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::VALIDATION_SOUTENANCE_AVANT_PRESOUTENANCE, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($emails)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationPresoutenance($these): Notification
    {
        $emails = $this->emailTheseService->fetchEmailAspectsDoctorat($these);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la maison du doctorat de la thèse {$these->getId()}");
        }

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'etablissement' => $these->getEtablissement()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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

        $refus = new StringElement(); $refus->texte = $motif;
        $vars = ['individu' => $currentUser, 'role' => $currentRole, 'etablissement' => $currentRole->getStructure(), 'stringelement' => $refus, 'these' => $these, 'doctorant' => $these->getDoctorant()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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
        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'rapporteur' => $membre];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DEMANDE_ENGAGEMENT_IMPARTIALITE, $vars);
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

    public function createNotificationSignatureEngagementImpartialite(These $these, Membre $membre): Notification
    {
        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'membre' => $membre];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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
        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'membre' => $membre];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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
        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'membre' => $membre];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'membre' => $avis->getMembre(), 'acteur' => $avis->getRapporteur(), 'avis' => $avis, 'etablissement' => $these->getEtablissement()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'membre' => $avis->getMembre(), 'acteur' => $avis->getRapporteur(), 'avis' => $avis, 'etablissement' => $these->getEtablissement()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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
        $email = $rapporteur->getEmail();
        if ($email === null) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la notification [" . MailTemplates::DEMANDE_PRERAPPORT . "] la thèse {$these->getId()}");
        }

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'rapporteur' => $rapporteur];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DEMANDE_PRERAPPORT, $vars);
        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    public function createNotificationFeuVertSoutenance(These $these): Notification
    {
        $emailsActeurs = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailsActeurs, $emailsED, $emailsUR);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la notification [" . MailTemplates::SOUTENANCE_FEU_VERT . "] la thèse {$these->getId()}");
        }

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DEMANDE_PRERAPPORT, $vars);
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

        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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
        $doctorant = $these->getDoctorant();
        $etablissement = $these->getEtablissement();

        $vars = ['soutenance' => $proposition, 'these' => $these, 'doctorant' => $doctorant, 'rapporteur' => $rapporteur, 'etablissement' => $etablissement];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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
        $doctorant = $these->getDoctorant();
        $etablissement = $these->getEtablissement();

        $vars = ['soutenance' => $proposition, 'these' => $these, 'doctorant' => $doctorant, 'rapporteur' => $membre, 'etablissement' => $etablissement];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::DEMANDE_RAPPORT_SOUTENANCE, $vars);

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($email)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    /** Mails de fin de procédure *************************************************************************************/


    /**
     * TODO
     */
    public function createNotificationEnvoiConvocationDoctorant(Doctorant $doctorant, Proposition $proposition, DateTime $date, string $email, string $url, array $avisArray): Notification
    {
        $these = $proposition->getThese();
        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);

        if ($email === null or $email === '') {
            throw new RuntimeException("Aucune adresse électronique pour le doctorant [" . $doctorant->getIndividu()->getNomComplet() . "]");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("L'adresse électronique est mal formée pour le doctorant [" . $doctorant->getIndividu()->getNomComplet() . "]");
        }

        $notif = new Notification();
        $notif
            ->setSubject("Convocation pour la soutenance de thèse de  " . $doctorant->getNomComplet())
            ->setTo($email)
            ->setTemplatePath('soutenance/notification/convocation-doctorant')
            ->setTemplateVariables([
                'these' => $proposition->getThese(),
                'proposition' => $proposition,
                'doctorant' => $doctorant,
                'date' => $date,
                'url' => $url,
                'informations' => $pdcData,

                'avisArray' => $avisArray,

            ]);

        return $notif;
    }

    /**
     * TODO
     */
    public function createNotificationEnvoiConvocationMembre(Membre $membre, Proposition $proposition, DateTime $date, string $email, string $url, array $avisArray): Notification
    {
        $doctorant = $proposition->getThese()->getDoctorant();
        $these = $proposition->getThese();
        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);

        if ($email === null or $email === '') {
            throw new RuntimeException("Aucune adresse électronique pour le membre [" . $membre->getDenomination() . "]");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("L'adresse électronique est mal formée pour le membre [" . $membre->getDenomination() . "]");
        }

        $notif = new Notification();
        $notif
            ->setSubject("Convocation pour la soutenance de thèse de  " . $doctorant->getNomComplet())
            ->setTo($email)
            ->setTemplatePath('soutenance/notification/convocation-membre')
            ->setTemplateVariables([
                'these' => $these,
                'proposition' => $proposition,
                'informations' => $pdcData,
                'date' => $date,
                'membre' => $membre,
                'url' => $url,

                'avisArray' => $avisArray,
            ]);

        return $notif;
    }

    public function createNotificationTransmettreDocumentsDirectionThese(These $these, Proposition $proposition): Notification
    {
        $vars = ['these' => $these, 'proposition' => $proposition, 'doctorant' => $these->getDoctorant()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

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
}