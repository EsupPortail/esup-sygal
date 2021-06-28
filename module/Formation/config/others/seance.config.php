<?php

namespace Formation;

use Formation\Controller\SeanceController;
use Formation\Controller\SeanceControllerFactory;
use Formation\Form\Seance\SeanceForm;
use Formation\Form\Seance\SeanceFormFactory;
use Formation\Form\Seance\SeanceHydrator;
use Formation\Form\Seance\SeanceHydratorFactory;
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
                        'modifier',
                        'historiser',
                        'restaurer',
                        'supprimer',
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
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter/:session',
                                    'defaults' => [
                                        'controller' => SeanceController::class,
                                        'action'     => 'ajouter',
                                    ],
                                ],
                            ],
                            'modifier' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/modifier/:seance',
                                    'defaults' => [
                                        'controller' => SeanceController::class,
                                        'action'     => 'modifier',
                                    ],
                                ],
                            ],
                            'historiser' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/historiser/:seance',
                                    'defaults' => [
                                        'controller' => SeanceController::class,
                                        'action'     => 'historiser',
                                    ],
                                ],
                            ],
                            'restaurer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/restaurer/:seance',
                                    'defaults' => [
                                        'controller' => SeanceController::class,
                                        'action'     => 'restaurer',
                                    ],
                                ],
                            ],
                            'supprimer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/supprimer/:seance',
                                    'defaults' => [
                                        'controller' => SeanceController::class,
                                        'action'     => 'supprimer',
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
        'factories' => [
            SeanceForm::class => SeanceFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            SeanceHydrator::class => SeanceHydratorFactory::class,
        ],
    ]

];