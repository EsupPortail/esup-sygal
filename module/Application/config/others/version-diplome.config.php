<?php

namespace Application;

use Application\Service\VersionDiplome\VersionDiplomeService;
use Application\Service\VersionDiplome\VersionDiplomeServiceFactory;
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
    'service_manager' => [
        'factories' => [
            VersionDiplomeService::class => VersionDiplomeServiceFactory::class,
        ],
    ],
];