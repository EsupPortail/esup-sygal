<?php

namespace Soutenance\Service\Notification;

trait SoutenanceNotificationFactoryAwareTrait
{
    protected SoutenanceNotificationFactory $soutenanceNotificationFactory;

    public function setSoutenanceNotificationFactory(SoutenanceNotificationFactory $soutenanceNotificationFactory): void
    {
        $this->soutenanceNotificationFactory = $soutenanceNotificationFactory;
    }
}