<?php

namespace Depot\Service\Notification;

use Depot\Service\Notification\NotificationFactory;

trait NotificationFactoryAwareTrait
{
    /**
     * @var NotificationFactory
     */
    protected $notificationFactory;

    /**
     * @param NotificationFactory $notificationFactory
     */
    public function setNotificationFactory(NotificationFactory $notificationFactory)
    {
        $this->notificationFactory = $notificationFactory;
    }
}