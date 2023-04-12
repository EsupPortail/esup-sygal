<?php

namespace Application;

use Application\Service\Notification\ApplicationNotificationFactory;
use Application\Service\Notification\ApplicationNotificationFactoryFactory;

return [
    'service_manager' => [
        'factories' => [
            ApplicationNotificationFactory::class => ApplicationNotificationFactoryFactory::class,
        ],
    ],
];