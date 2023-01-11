<?php

namespace StepStar\Service\Notification;

use Application\Service\Notification\NotifierServiceAwareTrait;
use StepStar\Notification\EnvoisEnErreurNotification;

class NotificationService
{
    use NotifierServiceAwareTrait;

    /**
     * @param \StepStar\Entity\Db\Log[] $logs
     * @return \StepStar\Notification\EnvoisEnErreurNotification
     */
    public function createEnvoisEnErreurNotification(array $logs): EnvoisEnErreurNotification
    {
        $notif = new EnvoisEnErreurNotification();
        $notif->setLogs($logs);

        return $notif;
    }

    /**
     * @param \StepStar\Notification\EnvoisEnErreurNotification $notif
     * @throws \Notification\Exception\NotificationException
     */
    public function sendEnvoisEnErreurNotification(EnvoisEnErreurNotification $notif)
    {
        $this->applicationNotifierService->trigger($notif);
    }
}