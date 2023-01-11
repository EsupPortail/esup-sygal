<?php

namespace Depot\Service\Notification;

use Application\Entity\Db\Role;
use Application\Entity\Db\Utilisateur;
use Application\Service\Email\EmailTheseServiceAwareTrait;
use Application\Service\Role\RoleServiceAwareTrait;
use Application\Service\Variable\VariableServiceAwareTrait;
use Depot\Notification\ValidationDepotTheseCorrigeeNotification;
use Depot\Notification\ValidationPageDeCouvertureNotification;
use Depot\Notification\ValidationRdvBuNotification;
use Individu\Service\IndividuServiceAwareTrait;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Helper\Url as UrlHelper;
use Notification\Notification;
use Structure\Service\EcoleDoctorale\EcoleDoctoraleServiceAwareTrait;
use Structure\Service\UniteRecherche\UniteRechercheServiceAwareTrait;
use These\Entity\Db\These;

/**
 * Service d'envoi de notifications par mail.
 *
 * @method NotificationFactory getNotificationFactory()
 *
 * @author Unicaen
 */
class NotifierService extends \Notification\Service\NotifierService
{
    use ListenerAggregateTrait;

    use VariableServiceAwareTrait;
    use EcoleDoctoraleServiceAwareTrait;
    use UniteRechercheServiceAwareTrait;
    use RoleServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use EmailTheseServiceAwareTrait;

    /**
     * @var UrlHelper
     */
    protected $urlHelper;

    /**
     * Notification à l'issue de la validation de la page de couverture.
     *
     * @param \These\Entity\Db\These $these
     * @param string $action
     * @throws \Notification\Exception\NotificationException
     */
    public function triggerValidationPageDeCouvertureNotification(These $these, string $action)
    {
        $notification = new ValidationPageDeCouvertureNotification();
        $notification->setThese($these);
        $notification->setAction($action);
        $notification->setEmailBu($this->emailTheseService->fetchEmailBu($these));

        $this->trigger($notification);
    }

    /**
     * Notification concernant la validation à l'issue du RDV BU.
     *
     * @param \Depot\Notification\ValidationRdvBuNotification $notification
     * @throws \Notification\Exception\NotificationException
     */
    public function triggerValidationRdvBu(ValidationRdvBuNotification $notification)
    {
        $these = $notification->getThese();

        $notification->setEmailBdd($this->emailTheseService->fetchEmailBdd($these));
        $notification->setEmailBu($this->emailTheseService->fetchEmailBu($these));

        $this->trigger($notification);
    }

    /**
     * Notification pour inviter à valider les corrections.
     *
     * @param These $these
     * @param \Application\Entity\Db\Utilisateur|null $utilisateur
     * @throws \Notification\Exception\NotificationException
     */
    public function triggerValidationDepotTheseCorrigee(These $these, ?Utilisateur $utilisateur = null)
    {
        $targetedUrl = $this->urlHelper->__invoke( 'these/validation-these-corrigee', ['these' => $these->getId()], ['force_canonical' => true]);
        $president = $this->getRoleService()->getRepository()->findOneByCodeAndStructureConcrete(Role::CODE_PRESIDENT_JURY, $these->getEtablissement());
        $url = $this->urlHelper->__invoke('zfcuser/login', ['type' => 'local'], ['query' => ['redirect' => $targetedUrl, 'role' => $president->getRoleId()], 'force_canonical' => true], true);

        // envoi de mail aux directeurs de thèse
        $notif = new ValidationDepotTheseCorrigeeNotification();
        $notif
            ->setThese($these)
            ->setEmailBdd($this->emailTheseService->fetchEmailBdd($these))
            ->setTemplateVariables([
                'these' => $these,
                'url'   => $url,
            ]);
        if ($utilisateur !== null) {
            $notif->setDestinataire($utilisateur);
        }

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
     * Notification à propos de la validation des corrections attendues.
     *
     * @param Notification $notif
     * @param These $these
     * @throws \Notification\Exception\NotificationException
     */
    public function triggerValidationCorrectionThese(Notification $notif, These $these)
    {
        $to = $this->emailTheseService->fetchEmailBdd($these);
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
     * @param These $these
     * @throws \Notification\Exception\NotificationException
     */
    public function triggerValidationCorrectionTheseEtudiant(Notification $notif, These $these)
    {
        $individu = $these->getDoctorant()->getIndividu();
        $to = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();
        if (!$to) {
            $this->messageContainer->setMessage("Impossible d'envoyer un mail à {$these->getDoctorant()} car son adresse est inconnue", 'danger');

            return;
        }
        $notif->setTo($to);

        $this->trigger($notif);

        $infoMessage = sprintf("Un mail de notification vient d'être envoyé au doctorant (%s)", $to);
        if ($this->messageContainer->getMessage()) {
            $new_message = "<ul><li>" . $this->messageContainer->getMessage() . "</li><li>" . $infoMessage . "</li></ul>";
            $this->messageContainer->setMessage($new_message, 'info');
        } else {
            $this->messageContainer->setMessage($infoMessage, 'info');
        }
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
}