<?php

namespace Application;

use Application\Controller\Factory\OffreControllerFactory;
use Application\Controller\OffreController;
use Laminas\Router\Http\Literal;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => OffreController::class,
                    'action'     => [
                        'index',
                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'offre-these' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/offre-these',
                    'defaults' => [
                        'controller'    => OffreController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            OffreController::class => OffreControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
        ],
    ],
    'view_helpers' => [
        'factories' => [
        ],
    ],
];
