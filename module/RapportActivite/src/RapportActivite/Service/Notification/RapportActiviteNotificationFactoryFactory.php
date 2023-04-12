<?php

namespace RapportActivite\Service\Notification;

class RapportActiviteNotificationFactoryFactory
{
    public function __invoke(): RapportActiviteNotificationFactory
    {
        return new RapportActiviteNotificationFactory();
    }
}