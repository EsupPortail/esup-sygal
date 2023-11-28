<?php

namespace Admission\Service\Notification;

use Admission\Entity\Db\AdmissionValidation;
use Admission\Notification\AdmissionOperationAttenduNotification;
use Admission\Notification\AdmissionValidationAjouteeNotification;
use Admission\Notification\AdmissionValidationSupprimeeNotification;
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

    public function createNotificationOperationAttendue(): AdmissionOperationAttenduNotification
    {
        return new AdmissionOperationAttenduNotification();
    }

    public function createNotificationValidationAjoutee(AdmissionValidation $rapportActiviteValidation): AdmissionValidationAjouteeNotification
    {
        $notif = new AdmissionValidationAjouteeNotification();
        $notif->setAdmissionValidation($rapportActiviteValidation);

        return $notif;
    }

    public function createNotificationValidationSupprimee(AdmissionValidation $rapportActiviteValidation): AdmissionValidationSupprimeeNotification
    {
        $notif = new AdmissionValidationSupprimeeNotification();
        $notif->setAdmissionValidation($rapportActiviteValidation);

        return $notif;
    }

}