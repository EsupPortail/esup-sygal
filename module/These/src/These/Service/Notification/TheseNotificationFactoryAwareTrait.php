<?php

namespace These\Service\Notification;

trait TheseNotificationFactoryAwareTrait
{
    protected TheseNotificationFactory $theseNotificationFactory;

    public function setTheseNotificationFactory(TheseNotificationFactory $theseNotificationFactory): void
    {
        $this->theseNotificationFactory = $theseNotificationFactory;
    }
}