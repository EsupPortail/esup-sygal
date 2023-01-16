<?php

namespace These;

use These\Service\Notification\TheseNotificationFactory;
use These\Service\Notification\TheseNotificationFactoryFactory;

return [
    'service_manager' => [
        'factories' => [
            TheseNotificationFactory::class => TheseNotificationFactoryFactory::class,
        ],
    ],
];