<?php

namespace Soutenance\Service\Notification;

use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Validation;
use Application\Service\Email\EmailTheseServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Utilisateur\UtilisateurServiceAwareTrait;
use DateTime;
use Doctorant\Entity\Db\Doctorant;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
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
use UnicaenAuth\Entity\Db\RoleInterface;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class SoutenanceNotificationFactory extends NotificationFactory
{
    use ActeurServiceAwareTrait;
    use EmailTheseServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use MembreServiceAwareTrait;
    use RoleServiceAwareTrait;
    use TheseServiceAwareTrait;
    use EmailTheseServiceAwareTrait;
    use UtilisateurServiceAwareTrait;
    use RenduServiceAwareTrait;
    use UrlServiceAwareTrait;

    /**
     * @return string[]
     */
    protected function getEmailAdministrateurTechnique(): array
    {
        $individus = $this->individuService->getRepository()->findByRole(Role::CODE_ADMIN_TECH);
        $emails = [];
        foreach ($individus as $individu) {
            $email = $individu->getEmailUtilisateur();
            if ($email) $emails[] = $email;
        }
        return $emails;
    }

    /**
     * @param These $these
     * @param Validation $validation
     * @return Notification
     * @see Application/view/soutenance/notification/devalidation.phtml
     */
    public function createNotificationDevalidationProposition(These $these, Validation $validation): Notification
    {
        $vars = ['these' => $these, 'doctorant' => $these->getDoctorant(), 'validation' => $validation];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::SOUTENANCE_VALIDATION_ANNULEE, $vars);
        $mail = $validation->getIndividu()->getEmailUtilisateur();
        if ($mail === null) {
            throw new RuntimeException("Aucun mail trouvé pour ".$validation->getIndividu()->getNomComplet());
        }

        $notif = new Notification();
        $notif
            ->setSubject($rendu->getSujet())
            ->setTo($mail)
            ->setBody($rendu->getCorps());
        return $notif;
    }

    /**
     * @param These $these
     * @param Validation $validation
     * @return Notification
     * @see Application/view/soutenance/notification/validation-acteur.phtml
     */
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

    /**
     * @param These $these
     * @return Notification
     */
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

    /**
     * @param These $these
     * @return Notification
     */
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

    /**
     * @param These $these
     * @return Notification
     */
    public function createNotificationBureauDesDoctoratsProposition(These $these): Notification
    {
        $emails = $this->emailTheseService->fetchEmailAspectsDoctorat($these);

        if (empty($emails)) {
            throw new RuntimeException("Aucune adresse mail trouvée pour la maison du doctorat de la thèse {$these->getId()}");
        }

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

    /**
     * @param These $these
     * @return Notification
     */
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

    /**
     * @param $these
     * @return Notification
     */
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

    /**
     * @param These $these
     * @param Individu $currentUser
     * @param RoleInterface $currentRole
     * @param string $motif
     * @return Notification
     */
    public function createNotificationRefusPropositionSoutenance($these, $currentUser, $currentRole, $motif): Notification
    {
        $emails = $this->emailTheseService->fetchEmailActeursDirects($these);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Votre proposition de soutenance a été réfusée")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/refus')
                ->setTemplateVariables([
                    'acteur' => $currentUser,
                    'role' => $currentRole,
                    'motif' => $motif,
                    'these' => $these,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }

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
            throw new RuntimeException("Aucun mail trouvé pour la maison du doctorat de ". $these->getEtablissement()->getStructure()->getLibelle());
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

    /**
     * @param These $these
     */
    public function createNotificationAvisRendus($these): Notification
    {
        $email = $this->emailTheseService->fetchEmailAspectsDoctorat($these);

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Tous les avis de soutenance de la thèse de " . $these->getDoctorant()->getIndividu() . " ont été rendus.")
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/tous-avis-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }

    }

    /**
     * @param These $these
     * @param Avis $avis
     * @param string $url
     */
    public function createNotificationAvisFavorable($these, $avis, $url = null): Notification
    {
        $emailBDD = $this->emailTheseService->fetchEmailAspectsDoctorat($these);
        $emailsDirecteurs = $this->emailTheseService->fetchEmailEncadrants($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailBDD, $emailsDirecteurs, $emailsED, $emailsUR);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if ($emails !== []) {
            $notif = new Notification();
            $notif
                ->setSubject("Un avis de soutenance favorable de la thèse de " . $these->getDoctorant()->getIndividu() . " a été rendue.")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/avis-favorable')
                ->setTemplateVariables([
                    'these' => $these,
                    'avis' => $avis,
                    'url' => $url,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }

    }

    /**
     * @param These $these
     * @param Avis $avis
     * @param string $url
     */
    public function createNotificationAvisDefavorable($these, $avis, $url = null): Notification
    {
        $emailsDirecteurs = $this->emailTheseService->fetchEmailEncadrants($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailsDirecteurs, $emailsED, $emailsUR);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if ($emails !== []) {
            $notif = new Notification();
            $notif
                ->setSubject("Un avis de soutenance défavorable de la thèse de " . $these->getDoctorant()->getIndividu() . " a été rendue.")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/avis-defavorable')
                ->setTemplateVariables([
                    'these' => $these,
                    'avis' => $avis,
                    'url' => $url,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }

    }


    /**************************** présoutenance ***************************/

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $rapporteur
     * @param string $url
     * @return Notification
     */
    public function createNotificationDemandeAvisSoutenance(These $these, Proposition $proposition, Membre $rapporteur, string $url): Notification
    {
        $email = $rapporteur->getEmail();

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de l'avis de soutenance de la thèse de " . $these->getDoctorant()->getIndividu())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/demande-avis-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'membre' => $rapporteur,
                    'url' => $url,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Avis[] $avis
     */
    public function createNotificationFeuVertSoutenance($these, $proposition, $avis): Notification
    {
        $emailsActeurs = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailsActeurs, $emailsED, $emailsUR);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("La soutenance de " . $these->getDoctorant()->getIndividu() . " a été acceptée par votre établissement.")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/feu-vert-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'avis' => $avis,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     */
    public function createNotificationStopperDemarcheSoutenance($these, $proposition): Notification
    {
        $emailsActeurs = $this->emailTheseService->fetchEmailActeursDirects($these);
        $emailsED = $this->emailTheseService->fetchEmailEcoleDoctorale($these);
        $emailsUR = $this->emailTheseService->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailsActeurs, $emailsED, $emailsUR);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Les démarches de soutenance de " . $these->getDoctorant()->getIndividu() . " ont été stoppées par la maison du doctorats de votre établissement.")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/stopper-demarche-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }
    }

    /**
     * @param Proposition $proposition
     * @param Utilisateur $user
     * @param string $url
     * @return Notification
     */
    public function createNotificationConnexionRapporteur(Proposition $proposition, Utilisateur $user, string $url): Notification
    {
        $email = $user->getEmail();
        if ($email === null) {
            throw new RuntimeException("Aucun email de fourni !");
        }

        if (!empty($email)) {
            $notif = new Notification();
            $notif
                ->setSubject("Connexion en tant que rapporteur de la thèse de " . $proposition->getThese()->getDoctorant()->getIndividu()->getNomComplet())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/connexion-rapporteur')
                ->setTemplateVariables([
                    'proposition' => $proposition,
                    'these' => $proposition->getThese(),
                    'username' => $user->getUsername(),
                    'url' => $url,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $proposition->getThese()->getId() . ")");
        }
    }

    /**
     * @param Membre $membre
     * @param string $url
     */
    public function createNotificationNotificationRapporteurRetard($membre, $url): Notification
    {
        if ($membre->getActeur() === null) {
            throw new RuntimeException("Notification vers rapporteur [MembreId = " . $membre->getId() . "] impossible car aucun acteur n'est lié.");
        }

        $email = $membre->getEmail();
        if ($email === null) {
            throw new RuntimeException("Notification vers rapporteur [MembreId = " . $membre->getId() . "] impossible car aucun email est donné pour l'individu associé [IndividuId = " . $membre->getIndividu()->getId() . "].");
        }

        $these = $membre->getProposition()->getThese();
        $doctorant = $these->getDoctorant()->getIndividu();

        if (!empty($email)) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de rapport de présoutenance pour la thèse de " . $doctorant->getNomComplet())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/retard-rapporteur')
                ->setTemplateVariables([
                    'these' => $these,
                    'doctorant' => $doctorant,
                    'proposition' => $membre->getProposition(),
                    'url' => $url,
                ]);

            return $notif;
        } else {
            throw new RuntimeException("Aucun mail de disponible (" . __METHOD__ . "::TheseId#" . $these->getId() . ")");
        }
    }

    /**
     * @param Doctorant $doctorant
     * @param Proposition $proposition
     * @param DateTime $date
     * @param string $email
     * @param string $url
     * @param array $avisArray
     * @return Notification
     */
    public function createNotificationEnvoiConvocationDoctorant(Doctorant $doctorant, Proposition $proposition, DateTime $date, string $email, string $url, array $avisArray): Notification
    {
        $these = $proposition->getThese();
        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);

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
     * @param Membre $membre
     * @param Proposition $proposition
     * @param DateTime $date
     * @param string $email
     * @param string $url
     * @param array $avisArray
     * @return Notification
     */
    public function createNotificationEnvoiConvocationMembre(Membre $membre, Proposition $proposition, DateTime $date, string $email, string $url, array $avisArray): Notification
    {
        $doctorant = $proposition->getThese()->getDoctorant();
        $these = $proposition->getThese();
        $pdcData = $this->getTheseService()->fetchInformationsPageDeCouverture($these);

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

    public function createNotificationTransmettreDocumentsDirectionThese(These $these, Proposition $proposition) : Notification
    {
        $vars = ['these' => $these, 'proposition' => $proposition, 'doctorant' => $these->getDoctorant()];
        $url = $this->getUrlService()->setVariables($vars);
        $vars['Url'] = $url;

        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::TRANSMETTRE_DOCUMENTS_DIRECTION, $vars);
        $mail = $these->getDirecteursTheseEmails();
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