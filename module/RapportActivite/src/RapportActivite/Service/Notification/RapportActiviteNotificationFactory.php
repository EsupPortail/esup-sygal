<?php

namespace RapportActivite\Service\Notification;

use Notification\Factory\NotificationFactory;
use RapportActivite\Entity\Db\RapportActivite;
use RapportActivite\Entity\Db\RapportActiviteAvis;
use RapportActivite\Entity\Db\RapportActiviteValidation;
use RapportActivite\Notification\RapportActiviteAvisNotification;
use RapportActivite\Notification\RapportActiviteOperationAttenduNotification;
use RapportActivite\Notification\RapportActiviteSupprimeNotification;
use RapportActivite\Notification\RapportActiviteValidationAjouteeNotification;
use RapportActivite\Notification\RapportActiviteValidationSupprimeeNotification;

class RapportActiviteNotificationFactory extends NotificationFactory
{
    public function createNotificationOperationAttendue(): RapportActiviteOperationAttenduNotification
    {
        return new RapportActiviteOperationAttenduNotification();
    }

    public function createNotificationValidationAjoutee(RapportActiviteValidation $rapportActiviteValidation): RapportActiviteValidationAjouteeNotification
    {
        $notif = new RapportActiviteValidationAjouteeNotification();
        $notif->setRapportActiviteValidation($rapportActiviteValidation);

        return $notif;
    }

    public function createNotificationValidationSupprimee(RapportActiviteValidation $rapportActiviteValidation): RapportActiviteValidationSupprimeeNotification
    {
        $notif = new RapportActiviteValidationSupprimeeNotification();
        $notif->setRapportActiviteValidation($rapportActiviteValidation);

        return $notif;
    }

    /**
     * @deprecated
     */
    public function createNotificationRapportActiviteAvis(RapportActiviteAvis $rapportActiviteAvis): RapportActiviteAvisNotification
    {
        $notif = new RapportActiviteAvisNotification();
        $notif->setRapportActiviteAvis($rapportActiviteAvis);

        return $notif;
    }

    public function createNotificationRapportActiviteSupprime(RapportActivite $rapportActivite): RapportActiviteSupprimeNotification
    {
        $doctorant = $rapportActivite->getThese()->getDoctorant();
        $individu = $doctorant->getIndividu();
        $email = $individu->getEmailContact() ?: $individu->getEmailPro() ?: $individu->getEmailUtilisateur();

        $notif = new RapportActiviteSupprimeNotification();
        $notif->setRapportActivite($rapportActivite);
        $notif->setSubject("Rapport d'activité supprimé");
        $notif->setTo([$email => $doctorant->getIndividu()->getNomComplet()]);

        return $notif;
    }
}