<?php

namespace Notification\Service;

use Notification\Entity\NotifEntity;
use Notification\Entity\Service\NotifEntityServiceAwareTrait;
use Notification\Notification;

/**
 * Classe abstraite de construction de notifications par mail.
 *
 * @author Unicaen
 */
abstract class NotificationFactory
{
    use NotifEntityServiceAwareTrait;

    /**
     * @param string $code
     * @return Notification
     */
    protected function createNotification($code = null)
    {
        /** @var Notification $notification */
        $notification = new Notification($code);

        $this->initNotification($notification);

        return $notification;
    }

    /**
     * Initialisation indispensable d'une notification.
     *
     * @param Notification $notification
     */
    protected function initNotification(Notification $notification)
    {
        $this->injectNotifEntity($notification);
    }

    private function injectNotifEntity(Notification $notification)
    {
        if ($code = $notification->getCode()) {
            /** @var NotifEntity $entity */
            $entity = $this->notifEntityService->getRepository()->findOneBy(['code' => $code]);
            $notification->setNotifEntity($entity);
        }
    }
}