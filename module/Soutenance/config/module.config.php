<?php

use Soutenance\Controller\Factory\SoutenanceControllerFactory;
use Soutenance\Controller\SoutenanceController;
use Zend\Mvc\Router\Http\Segment;

return array(
    'bjyauthorize'    => [
        'guards' => [
            \UnicaenAuth\Guard\PrivilegeController::class => [
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                        'index',
                    ],
                    'roles' => [
                        'Administrateur technique',
                    ],
                ],
            ],
        ],
    ],
    'doctrine'     => [],

    'router' => [
        'routes' => [
            'soutenance' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/soutenance',
                    'defaults' => [
                        'controller' => SoutenanceController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [],

    ],
    'controllers' => [
        'factories' => [
            SoutenanceController::class => SoutenanceControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
);
