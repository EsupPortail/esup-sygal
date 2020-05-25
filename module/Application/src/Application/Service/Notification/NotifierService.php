<?php

namespace Application\Service\Notification;

use Application\Entity\Db\Individu;
use Application\Entity\Db\MailConfirmation;
use Application\Entity\Db\Role;
use Application\Entity\Db\These;
use Application\Entity\Db\Utilisateur;
use Application\Entity\Db\Variable;
use Application\Notification\CorrectionAttendueUpdatedNotification;
use Application\Notification\ResultatTheseAdmisNotification;
use Application\Notification\ResultatTheseModifieNotification;
use Application\Notification\ValidationDepotTheseCorrigeeNotification;
use Application\Notification\ValidationRdvBuNotification;
use Application\Rule\NotificationDepotVersionCorrigeeAttenduRule;
use Application\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Import\Model\ImportObservResult;
use Notification\Notification;
use UnicaenApp\Exception\LogicException;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\View\Helper\Url as UrlHelper;

/**
 * Service d'envoi de notifications par mail.
 *
 * @method NotificationFactory getNotificationFactory()
 *
 * @author Unicaen
 */
class NotifierService extends \Notification\Service\NotifierService
{
    use VariableServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use RoleServiceAwareTrait;
    use IndividuServiceAwareTrait;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * Notification concernant la validation à l'issue du RDV BU.
     *
     * @param ValidationRdvBuNotification $notification
     */
    public function triggerValidationRdvBu(ValidationRdvBuNotification $notification)
    {
        $these = $notification->getThese();

        $notification->setEmailBdd($this->fetchEmailBdd($these));
        $notification->setEmailBu($this->fetchEmailBu($these));

        $this->trigger($notification);
    }

    /**
     * Notification du BDD concernant l'évolution des résultats de thèses.
     *
     * @param array $data
     * @return ResultatTheseModifieNotification
     */
    public function triggerBdDUpdateResultat(array $data)
    {
        $these = current($data)['these'];

        $emailBdd = $this->fetchEmailBdd($these);
        $emailBu = $this->fetchEmailBu($these);

        $notif = new ResultatTheseModifieNotification();
        $notif->setData($data);
        $notif->setTo([$emailBdd, $emailBu]);

        $this->trigger($notif);

        return $notif;
    }

    /**
     * Notification des doctorants dont le résultat de la thèse est passé à Admis.
     *
     * @param array $data
     * @return ResultatTheseAdmisNotification[]
     */
    public function triggerDoctorantResultatAdmis(array $data)
    {
        $notifs = [];

        foreach ($data as $array) {
            $these = $array['these'];
            /* @var These $these */

            $emailBdd = $this->fetchEmailBdd($these);

            $notif = new ResultatTheseAdmisNotification();
            $notif->setThese($these);
            $notif->setEmailBdd($emailBdd);

            $this->trigger($notif);

            $notifs[] = $notif;
        }

        return $notifs;
    }

    /**
     * @param ImportObservResult $record
     * @param These $these
     * @param string $message
     * @return CorrectionAttendueUpdatedNotification|null
     */
    public function triggerCorrectionAttendue(ImportObservResult $record, These $these, &$message = null)
    {
        // interrogation de la règle métier pour savoir comment agir...
        $rule = new NotificationDepotVersionCorrigeeAttenduRule();
        $rule
            ->setThese($these)
            ->setDateDerniereNotif($record->getDateNotif())
            ->execute();
        $message = $rule->getMessage(' ');
        $estPremiereNotif = $rule->estPremiereNotif();
        $dateProchaineNotif = $rule->getDateProchaineNotif();

        if ($dateProchaineNotif === null) {
            return null;
        }

        $dateProchaineNotif->setTime(0, 0, 0);
        $now = (new \DateTime())->setTime(0, 0, 0);

        if ($now != $dateProchaineNotif) {
            return null;
        }

        $notif = new CorrectionAttendueUpdatedNotification();
        $notif
            ->setThese($these)
            ->setEstPremiereNotif($estPremiereNotif);

        $this->trigger($notif);

        return $notif;
    }

    /**
     * @param These $these
     */
    public function triggerDateButoirCorrectionDepassee(These $these)
    {
        $to = $this->fetchEmailBdd($these);

        $notif = new Notification();
        $notif
            ->setSubject("Corrections " . lcfirst($these->getCorrectionAutoriseeToString(true)) . " non faites")
            ->setTo($to)
            ->setTemplatePath('application/these/mail/notif-date-butoir-correction-depassee')
            ->setTemplateVariables([
                'these' => $these,
            ]);

        $this->trigger($notif);
    }

    /**
     * Notification par mail des directeurs de thèse pour les inviter à valider les corrections.
     *
     * @param These $these
     */
    public function triggerValidationDepotTheseCorrigee(These $these)
    {
        // envoi de mail aux directeurs de thèse
        $notif = new ValidationDepotTheseCorrigeeNotification();
        $notif
            ->setThese($these)
            ->setEmailBdd($this->fetchEmailBdd($these))
            ->setTemplateVariables([
                'these' => $these,
                'url'   => $this->urlHelper->__invoke(
                    'these/validation-these-corrigee',
                    ['these' => $these->getId()],
                    ['force_canonical' => true]),
            ]);

        $this->trigger($notif);

        $infoMessages = $notif->getInfoMessages();
        $this->messageContainer->setMessages([
            'info' => $infoMessages[0],
        ]);
        if ($errorMessages = $notif->getWarningMessages()) {
            $this->messageContainer->addMessages([
                'danger' => $errorMessages[0],
            ]);
        }
    }

    /**
     * @param Notification $notif
     * @param These        $these
     */
    public function triggerValidationCorrectionThese(Notification $notif, These $these)
    {
        $to = $this->fetchEmailBdd($these);
        $notif
            ->setTo($to)
            ->setTemplateVariables([
                'these' => $these,
            ]);

        $this->trigger($notif);

        $infoMessage = sprintf("Un mail de notification vient d'être envoyé à la Maison du doctorat (%s)", $to);
        $this->messageContainer->setMessage($infoMessage, 'info');
    }

    /**
     * @param Notification $notif
     * @param These        $these
     */
    public function triggerValidationCorrectionTheseEtudiant(Notification $notif, These $these)
    {
        $to = $these->getDoctorant()->getEmailPro() ?: $these->getDoctorant()->getIndividu()->getEmail();
        if (!$to) {
            $this->messageContainer->setMessage("Impossible d'envoyer un mail à {$these->getDoctorant()} car son adresse est inconnue", 'danger');

            return;
        }
        $notif->setTo($to);

        $this->trigger($notif);

        $infoMessage = sprintf("Un mail de notification vient d'être envoyé à votre doctorant (%s)", $to);
        if ($this->messageContainer->getMessage()) {
            $new_message = "<ul><li>" . $this->messageContainer->getMessage() . "</li><li>" . $infoMessage . "</li></ul>";
            $this->messageContainer->setMessage($new_message, 'info');
        } else {
            $this->messageContainer->setMessage($infoMessage, 'info');
        }
    }

    /**
     * @param MailConfirmation $mailConfirmation
     * @param string           $titre
     * @param string           $corps
     */
    public function triggerMailConfirmation(MailConfirmation $mailConfirmation, $titre, $corps)
    {
        $notif = new Notification();
        $notif
            ->setSubject($titre)
            ->setTo($mailConfirmation->getEmail())
            ->setTemplatePath('application/doctorant/empty-mail')
            ->setTemplateVariables([
                'destinataire' => $mailConfirmation->getIndividu()->getNomUsuel(),
                'titre'        => $titre,
                'corps'        => $corps,
            ]);
        $this->trigger($notif);
    }

    /**
     * @param These $these
     * @return string
     */
    private function fetchEmailBdd(These $these)
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BDD, $these);

        return $variable->getValeur();
    }

    /**
     * @param These $these
     * @return string
     */
    private function fetchEmailBu(These $these)
    {
        $variable = $this->variableService->getRepository()->findByCodeAndThese(Variable::CODE_EMAIL_BU, $these);

        return $variable->getValeur();
    }

    /**
     * @param UrlHelper $urlHelper
     */
    public function setUrlHelper(UrlHelper $urlHelper)
    {
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param FlashMessenger $flashMessenger
     * @param string         $namespacePrefix
     */
    public function feedFlashMessenger(FlashMessenger $flashMessenger, $namespacePrefix = '')
    {
        $notificationLogs = $this->getLogs();

        if (! empty($notificationLogs['info'])) {
            $flashMessenger->addMessage($notificationLogs['info'], $namespacePrefix . 'info');
        }
        if (! empty($notificationLogs['danger'])) {
            $flashMessenger->addMessage($notificationLogs['danger'], $namespacePrefix . 'danger');
        }
    }

    /**
     * @var string $type
     * @var Role $role
     * @var Individu $individu
     */
    public function triggerChangementRole($type, $role, $individu)
    {
        $mail = $individu->getEmail();

        $notif = new Notification();
        $notif
            ->setSubject("Modification de vos rôles dans l'application")
            ->setTo($mail)
            ->setTemplatePath('application/utilisateur/changement-role')
            ->setTemplateVariables([
                'type'         => $type,
                'role'         => $role,
                'individu'     => $individu,
            ]);
        $this->trigger($notif);
    }

    /**
     * @param Utilisateur $utilisateur
     * @param string $url
     */
    public function triggerInitialisationCompte($utilisateur, $url) {

        $email = $utilisateur->getEmail();
        if ($email === null) throw new LogicException("Aucun email de fourni !");

        $token = $utilisateur->getPasswordResetToken();
        if ($token === null) throw new LogicException("Aucun token de fourni !");

        if (!empty($email)) {
            $notif = new Notification();
            $notif
                ->setSubject("Initialisation de votre compte SyGAL")
                ->setTo($email)
                ->setTemplatePath('application/utilisateur/mail/init-compte')
                ->setTemplateVariables([
                    'username' => $utilisateur->getUsername(),
                    'url' => $url,
                ]);
            $this->trigger($notif);
        }
    }

    /**
     * @param Utilisateur $utilisateur
     * @param string $url
     */
    public function triggerResetCompte($utilisateur, $url) {

        $email = $utilisateur->getEmail();
        if ($email === null) throw new LogicException("Aucun email de fourni !");

        $token = $utilisateur->getPasswordResetToken();
        if ($token === null) throw new LogicException("Aucun token de fourni !");

        if (!empty($email)) {
            $notif = new Notification();
            $notif
                ->setSubject("Réinitialisation de votre mot de passe de votre compte SyGAL")
                ->setTo($email)
                ->setTemplatePath('application/utilisateur/mail/reinit-compte')
                ->setTemplateVariables([
                    'username' => $utilisateur->getUsername(),
                    'url' => $url,
                ]);
            $this->trigger($notif);
        }
    }
}