<?php

use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
            ],
        ],
    ],

    'router' => [
        'routes' => [
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                ],
            ],
        ],
    ],

    'service_manager' => [
        'abstract_factories' => [
            'Laminas\Cache\Service\StorageCacheAbstractServiceFactory',
            'Laminas\Log\LoggerAbstractServiceFactory',
        ],
        'factories' => [
            Import\Service\SynchroService::class => Import\Service\Factory\SynchroServiceFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
