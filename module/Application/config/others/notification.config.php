<?php

use Application\Service\Notification\NotifierService;
use Application\Service\Notification\NotifierServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            NotifierService::class                       => NotifierServiceFactory::class,
            \Notification\Service\NotifierService::class => NotifierServiceFactory::class, // substitution
        ],
    ],
];