<?php

namespace Application;

use Application\Controller\ActualiteController;
use Application\Controller\Factory\ActualiteControllerFactory;
use Application\Controller\Factory\OffreControllerFactory;
use Application\Controller\Factory\SoutenanceControllerFactory;
use Application\Controller\OffreController;
use Application\Controller\SoutenanceController;
use Application\Service\Actualite\ActualiteService;
use Application\Service\Actualite\ActualiteServiceFactory;
use Application\View\Helper\Actualite\ActualiteViewHelperFactory;
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
            'soutenances-a-venir' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/soutenances-a-venir',
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
