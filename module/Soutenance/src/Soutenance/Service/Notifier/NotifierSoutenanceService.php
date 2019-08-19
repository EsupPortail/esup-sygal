<?php

namespace Soutenance\Service\Notifier;

use Application\Entity\Db\Individu;
use Application\Entity\Db\IndividuRole;
use Application\Entity\Db\These;
use Application\Entity\Db\Validation;
use Application\Entity\Db\Variable;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Notification\Notification;
use Notification\Service\NotifierService;
use Soutenance\Entity\Avis;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use UnicaenAuth\Entity\Db\RoleInterface;
use Zend\View\Helper\Url as UrlHelper;

class NotifierSoutenanceService extends NotifierService {
    use RoleServiceAwareTrait;
    use VariableServiceAwareTrait;

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
    protected function fetchEmailActeursDirects(These $these)
    {
        $emails = [];
        $emails[] = $these->getDoctorant()->getIndividu()->getEmail();
        foreach ($these->getEncadrements() as $encadrant) {
            $emails[] = $encadrant->getIndividu()->getEmail();
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
        $emails = $these->getDirecteursTheseEmails();
        $emails[] = $these->getDoctorant()->getIndividu()->getEmail();

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
        /** @var IndividuRole[] $individuRoles */
        $individuRoles = $this->roleService->getIndividuRoleByStructure($these->getUniteRecherche()->getStructure());

        $emails = [];
        foreach ($individuRoles as $individuRole) {
            $emails[] = $individuRole->getIndividu()->getEmail();
        }

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });
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
        /** @var IndividuRole[] $individuRoles */
        $individuRoles = $this->roleService->getIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure());

        $emails = [];
        foreach ($individuRoles as $individuRole) {
            $emails[] = $individuRole->getIndividu()->getEmail();
        }
        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

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
        $emails = [];
        //structures
        $emails[]  = $this->fetchEmailBdd($these);
        /** @var IndividuRole $individuRole */
        foreach ($this->roleService->getIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure()) as $individuRole) $emails[] = $individuRole->getIndividu()->getEmail();
        foreach ($this->roleService->getIndividuRoleByStructure($these->getUniteRecherche()->getStructure()) as $individuRole) $emails[] = $individuRole->getIndividu()->getEmail();
        //acteurs
        $emails[]  = $these->getDoctorant()->getIndividu()->getEmail();
        foreach ($these->getDirecteursTheseEmails() as $email => $name) $emails[] = $email;

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
                ->setSubject("Vous pouvez procéder aux rensignement de la présoutenance")
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
        $emails   = $these->getDirecteursTheseEmails();
        $emails[] = $these->getDoctorant()->getIndividu()->getEmail();

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
        $email   = $membre->getActeur()->getIndividu()->getEmail();

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
        $email   = $membre->getActeur()->getIndividu()->getEmail();

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
        $email   = $rapporteur->getActeur()->getIndividu()->getEmail();

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
     * @param Proposition $proposition
     * @param Avis[] $avis
     */
    public function triggerFeuVertSoutenance($these, $proposition, $avis) {

        $emails = $this->fetchEmailActeursDirects($these);

        /** @var IndividuRole $individuRole */
        $individuRoles = $this->roleService->getIndividuRoleByStructure($these->getEcoleDoctorale()->getStructure());
        foreach ($individuRoles as $individuRole) {
            $emails[] = $individuRole->getIndividu()->getEmail();
        }

        $emails = array_filter($emails, function ($s) {
            return $s !== null;
        });

        if (!empty($emails)) {
            $notif = new Notification();
            $notif
                ->setSubject("La soutenance de ".$these->getDoctorant()->getIndividu()." a été accepté par la maison du doctorats de votre établissement.")
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
}