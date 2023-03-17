<?php

namespace Application;

use Application\Service\Discipline\DisciplineService;
use Application\Service\Discipline\DisciplineServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
            ],
        ],
    ],

    'router' => [
        'routes' => [
        ],
    ],

    'controllers' => [
        'factories' => [
        ],
    ],
    'form_elements' => [
        'factories' => [
        ],
    ],
    'hydrators' => [
        'factories' => [
        ],
    ],
    'service_manager' => [
        'factories' => [
            DisciplineService::class => DisciplineServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [],
    ],
];