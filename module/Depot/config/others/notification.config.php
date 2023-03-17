<?php

namespace Depot;

use UnicaenPrivilege\Guard\PrivilegeController;
use Depot\Controller\ObserverController;
use Depot\Service\Notification\DepotNotificationFactory;
use Depot\Service\Notification\DepotNotificationFactoryFactory;

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
            DepotNotificationFactory::class => DepotNotificationFactoryFactory::class,
        ],
    ],
];
