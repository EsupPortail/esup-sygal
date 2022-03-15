<?php

namespace Formation;

use Formation\Controller\SessionController;
use Formation\Controller\SessionControllerFactory;
use Formation\Form\Session\SessionForm;
use Formation\Form\Session\SessionFormFactory;
use Formation\Form\Session\SessionHydrator;
use Formation\Form\Session\SessionHydratorFactory;
use Formation\Provider\Privilege\SessionPrivileges;
use Formation\Service\Session\SessionService;
use Formation\Service\Session\SessionServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => SessionController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => [
                        SessionPrivileges::SESSION_INDEX,
                    ],
                ],
                [
                    'controller' => SessionController::class,
                    'action' => [
                        'afficher',
                        'generer-emargements',
                    ],
                    'privileges' => [
                        SessionPrivileges::SESSION_AFFICHER,
                    ],
                ],
                [
                    'controller' => SessionController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => [
                        SessionPrivileges::SESSION_AJOUTER,
                    ],
                ],
                [
                    'controller' => SessionController::class,
                    'action' => [
                        'modifier',
                        'changer-etat',
                    ],
                    'privileges' => [
                        SessionPrivileges::SESSION_MODIFIER,
                    ],
                ],
                [
                    'controller' => SessionController::class,
                    'action' => [
                        'historiser',
                        'restaurer',
                    ],
                    'privileges' => [
                        SessionPrivileges::SESSION_HISTORISER,
                    ],
                ],
                [
                    'controller' => SessionController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => [
                        SessionPrivileges::SESSION_SUPPRIMER,
                    ],
                ],
                [
                    'controller' => SessionController::class,
                    'action' => [
                        'classer-inscriptions',
                        'declasser-inscriptions',
                    ],
                    'privileges' => [
                        SessionPrivileges::SESSION_INSCRIPTION,
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
                                    'route'    => '/ajouter/:formation',
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
                            'supprimer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/supprimer/:session',
                                    'defaults' => [
                                        'controller' => SessionController::class,
                                        'action'     => 'supprimer',
                                    ],
                                ],
                            ],
                            'changer-etat' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/changer-etat/:session[/:etat]',
                                    'defaults' => [
                                        'controller' => SessionController::class,
                                        'action'     => 'changer-etat',
                                    ],
                                ],
                            ],
                            'classer-inscriptions' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/classer-inscriptions/:session',
                                    'defaults' => [
                                        'controller' => SessionController::class,
                                        'action'     => 'classer-inscriptions',
                                    ],
                                ],
                            ],
                            'declasser-inscriptions' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/declasser-inscriptions/:session',
                                    'defaults' => [
                                        'controller' => SessionController::class,
                                        'action'     => 'declasser-inscriptions',
                                    ],
                                ],
                            ],
                            'generer-emargements' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/generer-emargements/:session',
                                    'defaults' => [
                                        'controller' => SessionController::class,
                                        'action'     => 'generer-emargements',
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