<?php

namespace Admission\Service\Notification;

use Admission\Provider\Template\MailTemplates;
use Application\Service\UserContextServiceAwareTrait;
use Notification\Exception\RuntimeException;
use Notification\Notification;
use UnicaenRenderer\Service\Rendu\RenduServiceAwareTrait;
use Notification\Factory\NotificationFactory as NF;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class NotificationFactory extends NF
{
    use RenduServiceAwareTrait;
    use UserContextServiceAwareTrait;

    public function createNotificationEnvoyerMail(): Notification
    {
        $vars = [
        ];
        $rendu = $this->getRenduService()->generateRenduByTemplateCode(MailTemplates::ENVOYER_MAIL, $vars);
        $mail = $this->userContextService->getIdentityDb()->getEmail();

        if (!$mail) {
            throw new RuntimeException("Aucune adresse mail.");
        }

        $notif = new Notification();
        $notif
            ->setTo($mail)
            ->setSubject($rendu->getSujet())
            ->setBody($rendu->getCorps());

        return $notif;
    }
}