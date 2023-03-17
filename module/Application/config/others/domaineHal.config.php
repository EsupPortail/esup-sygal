<?php

namespace Application;

use Application\Service\DomaineHal\DomaineHalService;
use Application\Service\DomaineHal\DomaineHalServiceFactory;
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
            DomaineHalService::class => DomaineHalServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [],
    ],
];