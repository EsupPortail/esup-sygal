<?php

namespace Application;

use Application\Controller\Factory\SoutenanceControllerFactory;
use Application\Controller\SoutenanceController;
use Laminas\Router\Http\Literal;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => SoutenanceController::class,
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
            'soutenances-actuelles' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/soutenances-actuelles',
                    'defaults' => [
                        'controller'    => SoutenanceController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            SoutenanceController::class => SoutenanceControllerFactory::class,
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
