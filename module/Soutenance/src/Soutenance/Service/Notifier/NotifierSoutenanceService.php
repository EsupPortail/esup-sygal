<?php

namespace Soutenance\Service\Notifier;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\These;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Validation;
use Application\Entity\Db\Variable;
use Application\Service\Acteur\ActeurServiceAwareTrait;
use Application\Service\FichierThese\PdcData;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\These\TheseServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use DateTime;
use Notification\Notification;
use Notification\Service\NotifierService;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Service\Membre\MembreServiceAwareTrait;
use UnicaenApp\Exception\LogicException;
use UnicaenApp\Exception\RuntimeException;
use UnicaenAuth\Entity\Db\RoleInterface;
use Zend\View\Helper\Url as UrlHelper;

class NotifierSoutenanceService extends NotifierService {
    use ActeurServiceAwareTrait;
    use MembreServiceAwareTrait;
    use RoleServiceAwareTrait;
    use VariableServiceAwareTrait;
    use TheseServiceAwareTrait;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    public function setUrlHelper($urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param These $these
     * @return string
     */
    protected function fetchEmailBdd(These $these)
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BDD, $these);
        return $variable->getValeur();
    }

    /**
     * @param These $these
     * @return array
     */
    protected function fetchEmailEcoleDoctorale(These $these)
    {
        /** @var IndividuRole[] $individuRoles */
        $individuRoles = $this->roleService->getIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure());

        $emails = [];
        foreach ($individuRoles as $individuRole) {
            if ($individuRole->getIndividu()->getEmail() !== null)
                $emails[] = $individuRole->getIndividu()->getEmail();
        }
        return $emails;
    }


    /**
     * @param These $these
     * @return string[]
     */
    protected function fetchEmailUniteRecherche(These $these)
    {
        /** @var IndividuRole[] $individuRoles */
        $individuRoles = $this->roleService->getIndividuRoleByStructure($these->getUniteRecherche()->getStructure());

        $emails = [];
        foreach ($individuRoles as $individuRole) {
            if ($individuRole->getIndividu()->getEmail() !== null)
                $emails[] = $individuRole->getIndividu()->getEmail();
        }
        return $emails;
    }

    /**
     * @param These $these
     * @return string[]
     */
    protected function fetchEmailEncadrants(These $these) {
        $emails = [];
        $encadrants = $this->getActeurService()->getRepository()->findEncadrementThese($these);
        foreach ($encadrants as $encadrant) {
            $email = $encadrant->getIndividu()->getEmail();
            if ($email === null) {
                $membre = $this->getMembreService()->getMembreByActeur($encadrant);
                if ($membre) $email = $membre->getEmail();
            }
            $emails[] = $email;
        }
        return $emails;
    }

    /**
     * @param These $these
     * @return array
     */
    protected function fetchEmailActeursDirects(These $these)
    {
        $emails = [];
        $emails[] = $these->getDoctorant()->getIndividu()->getEmail();

        $encadrants = $this->fetchEmailEncadrants($these);
        foreach ($encadrants as $encadrant) {
            $emails[] = $encadrant;
        }
        return $emails;
    }

    /**
     * @see Application/view/soutenance/notification/devalidation.phtml
     * @param Validation $validation
     */
    public function triggerDevalidationProposition($validation) {
        $mail = $validation->getIndividu()->getEmail();
        $these = $validation->getThese();

        if ($mail !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Votre validation de la proposition de soutenance a été annulée")
                ->setTo($mail)
                ->setTemplatePath('soutenance/notification/devalidation')
                ->setTemplateVariables([
                    'validation' => $validation,
                    'these' => $these,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @see Application/view/soutenance/notification/validation-acteur.phtml
     * @param These $these
     * @param Validation $validation
     */
    public function triggerValidationProposition($these, $validation)
    {
        $emails = $this->fetchEmailActeursDirects($these);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Une validation de votre proposition de soutenance vient d'être faite")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/validation-acteur')
                ->setTemplateVariables([
                    'validation' => $validation,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @see Application/view/soutenance/notification/validation-structure.phtml
     * @param These $these
     */
    public function triggerNotificationUniteRechercheProposition($these)
    {
        $emails = $this->fetchEmailUniteRecherche($these);

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de validation d'une proposition de soutenance")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/validation-structure')
                ->setTemplateVariables([
                    'these' => $these,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @see Application/view/soutenance/notification/validation-structure.phtml
     * @param These $these
     */
    public function triggerNotificationEcoleDoctoraleProposition($these)
    {
        $emails = $this->fetchEmailEcoleDoctorale($these);

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de validation d'une proposition de soutenance")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/validation-structure')
                ->setTemplateVariables([
                    'these' => $these,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @see Application/view/soutenance/notification/validation-structure.phtml
     * @param These $these
     */
    public function triggerNotificationBureauDesDoctoratsProposition($these)
    {
        $email = $this->fetchEmailBdd($these);

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de validation d'une proposition de soutenance")
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/validation-structure')
                ->setTemplateVariables([
                    'these' => $these,
                ]);
            $this->trigger($notif);
        }
    }

    /** @param These $these */
    public function triggerNotificationPropositionValidee($these)
    {
        $emailsBDD      = [ $this->fetchEmailBdd($these) ];
        $emailsED       = $this->fetchEmailEcoleDoctorale($these);
        $emailsUR       = $this->fetchEmailUniteRecherche($these);
        $emailsActeurs  = $this->fetchEmailActeursDirects($these);
        $emails = array_merge($emailsBDD, $emailsED, $emailsUR, $emailsActeurs);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de validation d'une proposition de soutenance")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/validation-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                ]);
            $this->trigger($notif);
        }
    }

    /** @param These $these */
    public function triggerNotificationPresoutenance($these)
    {
        $email = $this->fetchEmailBdd($these);

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Vous pouvez procéder au renseignement des informations de soutenance")
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/presoutenance')
                ->setTemplateVariables([
                    'these' => $these,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     * @param Individu $currentUser
     * @param RoleInterface $currentRole
     * @param string $motif
     */
    public function triggerRefusPropositionSoutenance($these, $currentUser, $currentRole, $motif)
    {
        $emails = $this->fetchEmailActeursDirects($these);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Votre proposistion de soutenance a été réfusé")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/refus')
                ->setTemplateVariables([
                    'acteur' => $currentUser,
                    'role' => $currentRole,
                    'motif' => $motif,
                    'these' => $these,
                ]);
            $this->trigger($notif);
        }
    }

    /** ENGAGEMENT IMPARTIALITE ***************************************************************************************/

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function triggerDemandeSignatureEngagementImpartialite($these, $proposition, $membre)
    {
        $email   = $membre->getIndividu()->getEmail();

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Demande de signature de l'engagement d'impartialité de la thèse de " . $these->getDoctorant()->getIndividu())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/engagement-impartialite-demande')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'membre' => $membre,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function triggerSignatureEngagementImpartialite($these, $proposition, $membre)
    {
        $email   = $this->fetchEmailBdd($these);

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Signature de l'engagement d'impartialité de la thèse de " . $these->getDoctorant()->getIndividu())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/engagement-impartialite-signature')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'membre' => $membre,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function triggerRefusEngagementImpartialite($these, $proposition, $membre)
    {

        $emails  = $this->fetchEmailActeursDirects($these);
        $emails[] = $this->fetchEmailBdd($these);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Refus de l'engagement d'impartialité de la thèse de " . $these->getDoctorant()->getIndividu())
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/engagement-impartialite-refus')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'membre' => $membre,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $membre
     */
    public function triggerAnnulationEngagementImpartialite($these, $proposition, $membre)
    {
        $email   = $membre->getIndividu()->getEmail();

        if ($email) {
            $notif = new Notification();
            $notif
                ->setSubject("Annulation de l'engagement d'impartialité de la thèse de " . $these->getDoctorant()->getIndividu())
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/engagement-impartialite-annulation')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'membre' => $membre,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Membre $rapporteur
     */
    public function triggerDemandeAvisSoutenance($these, $proposition, $rapporteur)
    {
        $email   = $rapporteur->getIndividu()->getEmail();

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
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     */
    public function triggerAvisRendus($these)
    {
        $email = $this->fetchEmailBdd($these);

        if ($email !== null) {
            $notif = new Notification();
            $notif
                ->setSubject("Tous les avis de soutenance de la thèse de " . $these->getDoctorant()->getIndividu() . " ont été rendus.")
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/tous-avis-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     * @param Avis $avis
     * @param string $url
     */
    public function triggerAvisFavorable($these, $avis, $url)
    {
        $emailBDD           = [ $this->fetchEmailBdd($these) ];
        $emailsDirecteurs   = $this->fetchEmailEncadrants($these);
        $emailsED           = $this->fetchEmailEcoleDoctorale($these);
        $emailsUR           = $this->fetchEmailUniteRecherche($these);
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
            $this->trigger($notif);
        }
    }
    /**
     * @param These $these
     * @param Avis $avis
     * @param string $url
     */
    public function triggerAvisDefavorable($these, $avis, $url)
    {
        $emailsDirecteurs   = $this->fetchEmailEncadrants($these);
        $emailsED           = $this->fetchEmailEcoleDoctorale($these);
        $emailsUR           = $this->fetchEmailUniteRecherche($these);
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
            $this->trigger($notif);
        }
    }


    /**
     * @param These $these
     * @param Proposition $proposition
     * @param Avis[] $avis
     */
    public function triggerFeuVertSoutenance($these, $proposition, $avis) {

        $emailsActeurs      = $this->fetchEmailActeursDirects($these);
        $emailsED           = $this->fetchEmailEcoleDoctorale($these);
        $emailsUR           = $this->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailsActeurs, $emailsED, $emailsUR);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("La soutenance de ".$these->getDoctorant()->getIndividu()." a été accepté par la maison du doctorat de votre établissement.")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/feu-vert-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                    'avis' => $avis,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     * @param Proposition $proposition
     */
    public function triggerStopperDemarcheSoutenance($these, $proposition) {

        $emailsActeurs      = $this->fetchEmailActeursDirects($these);
        $emailsED           = $this->fetchEmailEcoleDoctorale($these);
        $emailsUR           = $this->fetchEmailUniteRecherche($these);
        $emails = array_merge($emailsActeurs, $emailsED, $emailsUR);

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("Les démarches de soutenance de ".$these->getDoctorant()->getIndividu()." ont été stoppées par la maison du doctorats de votre établissement.")
                ->setTo($emails)
                ->setTemplatePath('soutenance/notification/stopper-demarche-soutenance')
                ->setTemplateVariables([
                    'these' => $these,
                    'proposition' => $proposition,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param These $these
     * @param Utilisateur $utilisateur
     * @param string $url
     */
    public function triggerInitialisationCompte($these, $utilisateur, $url) {

        $email = $utilisateur->getEmail();
        if ($email === null) throw new LogicException("Aucun email de fourni !");

        $token = $utilisateur->getPasswordResetToken();
        if ($token === null) throw new LogicException("Aucun token de fourni !");

        if (!empty($email)) {
            $notif = new Notification();
            $notif
                ->setSubject("Initialisation de votre compte SyGAL")
                ->setTo($email)
                ->setTemplatePath('soutenance/notification/init-compte')
                ->setTemplateVariables([
                    'these' => $these,
                    'username' => $utilisateur->getUsername(),
                    'url' => $url,
                ]);
            $this->trigger($notif);
        }
    }


    /**
     * @param Notification $notification
     */
    public function trigger(Notification $notification)
    {
        $notification->prepare();

        $this->sendNotification($notification);

        // collecte des éventuels messages exposés par la notification
        foreach ($notification->getInfoMessages() as $message) {
            $this->messageContainer->setMessage($message, 'info');
        }
        foreach ($notification->getWarningMessages() as $message) {
            $this->messageContainer->setMessage($message, 'warning');
        }
    }

    /**
     * @param Membre $membre
     * @param string $url
     */
    public function triggerNotificationRapporteurRetard($membre, $url)
    {
        if ($membre->getActeur() === null) throw new RuntimeException("Notification vers rapporteur [MembreId = ".$membre->getId()."] impossible car aucun acteur n'est lié.");

        $email = $membre->getIndividu()->getEmail();
        if ($email === null) throw new RuntimeException("Notification vers rapporteur [MembreId = ".$membre->getId()."] impossible car aucun email est donné pour l'individu associé [IndividuId = ".$membre->getIndividu()->getId()."].");


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
            $this->trigger($notif);
        }
    }

    /**
     * @param Doctorant $doctorant
     * @param Proposition $proposition
     * @param DateTime $date
     * @param string $email
     * @param string $url
     */
    public function triggerEnvoiConvocationDoctorant(Doctorant $doctorant, Proposition $proposition, DateTime $date, $email, $url)
    {
        if ($email === null) throw new LogicException("Aucun mail n'est fourni pour l'envoi de la convocation.",0);

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
            ]);
        $this->trigger($notif);
    }

    /**
     * @param Membre $membre
     * @param Proposition $proposition
     * @param DateTime $date
     * @param string $email
     * @param string $url
     */
    public function triggerEnvoiConvocationMembre(Membre $membre, Proposition $proposition, DateTime $date, $email, $url)
    {
        if ($email === null) throw new LogicException("Aucun mail n'est fourni pour l'envoi de la convocation.",0);

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
            ]);
        $this->trigger($notif);
    }

}