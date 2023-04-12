<?php

namespace Formation\Service\Notification;

trait FormationNotificationFactoryAwareTrait
{
    protected FormationNotificationFactory $formationNotificationFactory;

    public function setFormationNotificationFactory(FormationNotificationFactory $formationNotificationFactory): void
    {
        $this->formationNotificationFactory = $formationNotificationFactory;
    }
}