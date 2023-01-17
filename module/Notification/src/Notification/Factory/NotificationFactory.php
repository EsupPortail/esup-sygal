<?php

namespace Notification\Factory;

use Notification\Entity\NotifEntity;
use Notification\Entity\Service\NotifEntityServiceAwareTrait;
use Notification\Notification;

/**
 * Classe de construction de notifications par mail.
 *
 * @author Unicaen
 */
class NotificationFactory
{
    use NotifEntityServiceAwareTrait;

    /**
     * Instancie et initialise une nouvelle notification.
     */
    public function createNotification(?string $code = null): Notification
    {
        $notification = new Notification($code);

        $this->initNotification($notification);

        return $notification;
    }

    /**
     * Initialisation indispensable d'une notification.
     */
    public function initNotification(Notification $notification)
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