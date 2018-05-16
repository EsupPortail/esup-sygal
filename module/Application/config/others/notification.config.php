<?php

namespace Application;

use Application\Service\Notification\NotificationFactory;
use Application\Service\Notification\NotificationFactoryFactory;
use Application\Service\Notification\NotifierService;
use Application\Service\Notification\NotifierServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            NotificationFactory::class                   => NotificationFactoryFactory::class,
            NotifierService::class                       => NotifierServiceFactory::class,
            \Notification\Service\NotifierService::class => NotifierServiceFactory::class, // substitution
        ],
    ],
];