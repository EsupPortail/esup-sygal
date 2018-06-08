<?php

namespace Application\Service\Notification;

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