<?php

namespace RapportActivite\Service\Notification;

trait RapportActiviteNotificationFactoryAwareTrait
{
    protected RapportActiviteNotificationFactory $rapportActiviteNotificationFactory;

    public function setRapportActiviteNotificationFactory(RapportActiviteNotificationFactory $rapportActiviteNotificationFactory): void
    {
        $this->rapportActiviteNotificationFactory = $rapportActiviteNotificationFactory;
    }
}