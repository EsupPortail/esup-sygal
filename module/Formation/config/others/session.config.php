<?php

namespace Formation;

use Formation\Controller\FormationController;
use Formation\Controller\SessionController;
use Formation\Controller\SessionControllerFactory;
use Formation\Provider\Privilege\IndexPrivileges;
use Formation\Service\Session\SessionService;
use Formation\Service\Session\SessionServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => SessionController::class,
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
                            'session' => [
                                'label'    => 'Sessions',
                                'route'    => 'formation/session',
                                'resource' => PrivilegeController::getResourceId(SessionController::class, 'index') ,
                                'order'    => 200,
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
                    'session' => [
                        'type'  => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/session',
                            'defaults' => [
                                'controller' => SessionController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'ajouter' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter/:module',
                                    'defaults' => [
                                        'controller' => SessionController::class,
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
            SessionService::class => SessionServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            SessionController::class => SessionControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [],
    ],
    'hydrators' => [
        'factories' => [],
    ]

];