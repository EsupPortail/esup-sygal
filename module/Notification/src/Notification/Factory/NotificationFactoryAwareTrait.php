<?php

namespace Notification\Factory;

trait NotificationFactoryAwareTrait
{
    protected NotificationFactory $otificationFactory;

    public function setNotifierService(NotificationFactory $otificationFactory)
    {
        $this->otificationFactory = $otificationFactory;
    }
}