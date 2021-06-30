<?php

namespace Formation;

use Formation\Controller\SessionController;
use Formation\Controller\SessionControllerFactory;
use Formation\Form\Session\SessionForm;
use Formation\Form\Session\SessionFormFactory;
use Formation\Form\Session\SessionHydrator;
use Formation\Form\Session\SessionHydratorFactory;
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
                        'afficher',
                        'ajouter',
                        'modifier',
                        'historiser',
                        'restaurer',
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
                            'afficher' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/afficher/:session',
                                    'defaults' => [
                                        'controller' => SessionController::class,
                                        'action'     => 'afficher',
                                    ],
                                ],
                            ],
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
                            'modifier' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/modifier/:session',
                                    'defaults' => [
                                        'controller' => SessionController::class,
                                        'action'     => 'modifier',
                                    ],
                                ],
                            ],
                            'historiser' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/historiser/:session',
                                    'defaults' => [
                                        'controller' => SessionController::class,
                                        'action'     => 'historiser',
                                    ],
                                ],
                            ],
                            'restaurer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/restaurer/:session',
                                    'defaults' => [
                                        'controller' => SessionController::class,
                                        'action'     => 'restaurer',
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
        'factories' => [
            SessionForm::class => SessionFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            SessionHydrator::class => SessionHydratorFactory::class,
        ],
    ]

];