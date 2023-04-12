<?php

namespace Depot\Service\Notification;

trait DepotNotificationFactoryAwareTrait
{
    protected DepotNotificationFactory $depotNotificationFactory;

    public function setDepotNotificationFactory(DepotNotificationFactory $depotNotificationFactory): void
    {
        $this->depotNotificationFactory = $depotNotificationFactory;
    }
}