<?php

namespace Application\Service\Notification;

trait ApplicationNotificationFactoryAwareTrait
{
    protected ApplicationNotificationFactory $applicationNotificationFactory;

    public function setApplicationNotificationFactory(ApplicationNotificationFactory $applicationNotificationFactory): void
    {
        $this->applicationNotificationFactory = $applicationNotificationFactory;
    }
}