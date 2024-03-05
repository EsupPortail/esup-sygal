<?php

namespace Admission\Service\Notification;

trait NotificationFactoryAwareTrait
{
    protected NotificationFactory $notificationFactory;

    public function getNotificationFactory(): NotificationFactory
    {
        return $this->notificationFactory;
    }

    public function setNotificationFactory(NotificationFactory $notificationFactory): void
    {
        $this->notificationFactory = $notificationFactory;
    }

}