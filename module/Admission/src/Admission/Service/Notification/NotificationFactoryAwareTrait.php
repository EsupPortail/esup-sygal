<?php

namespace Admission\Service\Notification;

trait NotificationFactoryAwareTrait
{
    private NotificationFactory $notificationFactory;

    public function getNotificationFactory(): NotificationFactory
    {
        return $this->notificationFactory;
    }

    public function setNotificationFactory(NotificationFactory $notificationFactory): void
    {
        $this->notificationFactory = $notificationFactory;
    }

}