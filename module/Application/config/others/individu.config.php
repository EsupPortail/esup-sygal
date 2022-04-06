<?php

namespace Application;

use Application\Controller\Factory\IndividuControllerFactory;
use Application\Controller\IndividuController;
use Application\Service\Individu\IndividuService;
use Application\Service\Individu\IndividuServiceFactory;
use Laminas\Router\Http\Literal;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndividuController::class,
                    'action'     => [
                        'rechercher',
                    ],
                    'role' => [],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'individu' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/individu',
                    'defaults' => [
                        'controller'    => IndividuController::class,
//                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'rechercher' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/rechercher',
                            'defaults' => [
                                'controller'    => IndividuController::class,
                                'action'        => 'rechercher',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            IndividuController::class => IndividuControllerFactory::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
//            IndividuService::class => IndividuServiceFactory::class,
        ],
    ],
    'form_elements'   => [
        'factories' => [
        ],
    ],
    'hydrators' => [
        'factories' => [
        ],
    ],
];