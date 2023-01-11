<?php

namespace Depot;

use UnicaenAuth\Guard\PrivilegeController;
use Depot\Controller\ObserverController;
use Depot\Service\Notification\NotificationFactory;
use Depot\Service\Notification\NotificationFactoryFactory;
use Depot\Service\Notification\NotifierService;
use Depot\Service\Notification\NotifierServiceFactory;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ObserverController::class,
                    'action' => [
                        'notify-date-butoir-correction-depassee',
                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'notify-date-butoir-correction-depassee' => [
                    'options' => [
                        'route' => 'notify-date-butoir-correction-depassee',
                        'defaults' => [
                            'controller' => ObserverController::class,
                            'action' => 'notify-date-butoir-correction-depassee',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            NotificationFactory::class => NotificationFactoryFactory::class,
            NotifierService::class => NotifierServiceFactory::class,
        ],
    ],
];
