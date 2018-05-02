<?php

use Application\Service\Notification\NotificationService;
use Application\Service\Notification\NotificationServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            NotificationService::class => NotificationServiceFactory::class,
            \Notification\Service\NotificationService::class => NotificationServiceFactory::class, // substitution
        ],
    ],
];