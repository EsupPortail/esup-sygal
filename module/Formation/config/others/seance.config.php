<?php

namespace Formation;

use Formation\Controller\FormationController;
use Formation\Controller\SeanceController;
use Formation\Controller\SeanceControllerFactory;
use Formation\Provider\Privilege\IndexPrivileges;
use Formation\Service\Seance\SeanceService;
use Formation\Service\Seance\SeanceServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => SeanceController::class,
                    'action' => [
                        'index',
                        'ajouter',
                    ],
                    'privileges' => [
                        IndexPrivileges::INDEX_AFFICHER,
                    ],
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    'formation' => [
                        'pages' => [
                            'seance' => [
                                'label'    => 'Seances',
                                'route'    => 'formation/seance',
                                'resource' => PrivilegeController::getResourceId(SeanceController::class, 'index') ,
                                'order'    => 300,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'formation' => [
                'child_routes' => [
                    'seance' => [
                        'type'  => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/seance',
                            'defaults' => [
                                'controller' => SeanceController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'ajouter' => [
                                'type'  => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter',
                                    'defaults' => [
                                        'controller' => SeanceController::class,
                                        'action'     => 'ajouter',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            SeanceService::class => SeanceServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            SeanceController::class => SeanceControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [],
    ],
    'hydrators' => [
        'factories' => [],
    ]

];