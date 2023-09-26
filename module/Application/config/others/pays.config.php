<?php

use Application\Controller\Factory\PaysControllerFactory;
use Application\Controller\PaysController;
use Application\Service\Pays\PaysService;
use Application\Service\Pays\PaysServiceFactory;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            'BjyAuthorize\Guard\Controller' => [
                ['controller' => PaysController::class, 'action' => 'rechercher-pays', 'roles' => []],
                ['controller' => PaysController::class, 'action' => 'rechercher-nationalite', 'roles' => []],
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'pays' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/pays',
                    'defaults' => [
                        'controller' => PaysController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'rechercher-pays' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/rechercher-pays',
                            'defaults' => [
                                'action' => 'rechercher-pays',
                            ],
                        ],
                    ],
                    'rechercher-nationalite' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/rechercher-nationalite',
                            'defaults' => [
                                'action' => 'rechercher-nationalite',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            PaysService::class => PaysServiceFactory::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
        ],
        'factories' => [
            PaysController::class => PaysControllerFactory::class,
        ],
    ],
];
