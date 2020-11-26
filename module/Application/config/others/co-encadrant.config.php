<?php

namespace Application;

use Application\Controller\CoEncadrantController;
use Application\Controller\Factory\CoEncadrantControllerFactory;
use Application\Form\Factory\RechercherCoEncadrantFormFactory;
use Application\Form\RechercherCoEncadrantForm;
use Application\Service\CoEncadrant\CoEncadrantService;
use Application\Service\CoEncadrant\CoEncadrantServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => CoEncadrantController::class,
                    'action'     => [
                        'index',
                        'historique',
                        'rechercher-co-encadrant',
                        'ajouter-co-encadrant',
                        'retirer-co-encadrant',
                    ],
//                    'privileges' => [],
                    'roles' => [],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'co-encadrant' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/co-encadrant',
                    'defaults' => [
                        'controller'    => CoEncadrantController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'historique' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/historique/:co-encadrant',
                            'defaults' => [
                                'controller'    => CoEncadrantController::class,
                                'action'        => 'historique',
                            ],
                        ],
                    ],
                    'rechercher-co-encadrant' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/rechercher-co-encadrant',
                            'defaults' => [
                                'controller'    => CoEncadrantController::class,
                                'action'        => 'rechercher-co-encadrant',
                            ],
                        ],
                    ],
                    'ajouter-co-encadrant' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/ajouter-co-encadrant/:these/:co-encadrant',
                            'defaults' => [
                                'controller'    => CoEncadrantController::class,
                                'action'        => 'ajouter-co-encadrant',
                            ],
                        ],
                    ],
                    'retirer-co-encadrant' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/retirer-co-encadrant/:these/:co-encadrant',
                            'defaults' => [
                                'controller'    => CoEncadrantController::class,
                                'action'        => 'retirer-co-encadrant',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'co-encadrant' => [
                                'label'    => 'Co-encadrant',
                                'route'    => 'co-encadrant',
                                'resource' => PrivilegeController::getResourceId(CoEncadrantController::class, 'index'),

                                'order'    => 1000,
                                'pages' => [
                                    'historique' => [
                                        'label'    => 'Historique',
                                        'route'    => 'co-encadrant/historique',
                                        'resource' => PrivilegeController::getResourceId(CoEncadrantController::class, 'historique'),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            CoEncadrantController::class => CoEncadrantControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            RechercherCoEncadrantForm::class => RechercherCoEncadrantFormFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            CoEncadrantService::class => CoEncadrantServiceFactory::class,
        ],
    ],
];
