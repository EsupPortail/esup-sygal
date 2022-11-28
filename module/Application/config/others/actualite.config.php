<?php

namespace Application;

use Application\Controller\ActualiteController;
use Application\Controller\Factory\ActualiteControllerFactory;
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
                    'controller' => ActualiteController::class,
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
            'actualite' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/actualite',
                    'defaults' => [
                        'controller'    => ActualiteController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            ActualiteController::class => ActualiteControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            ActualiteService::class => ActualiteServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'actualite' => ActualiteViewHelperFactory::class,
        ],
    ],
];
