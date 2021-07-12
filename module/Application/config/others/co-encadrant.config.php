<?php

namespace Application;

use Application\Controller\CoEncadrantController;
use Application\Controller\Factory\CoEncadrantControllerFactory;
use Application\Form\Factory\RechercherCoEncadrantFormFactory;
use Application\Form\RechercherCoEncadrantForm;
use Application\Provider\Privilege\CoEncadrantPrivileges;
use Application\Service\CoEncadrant\CoEncadrantService;
use Application\Service\CoEncadrant\CoEncadrantServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

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
                        'generer-justificatif-coencadrements',
                        'generer-export-csv',
                    ],
                    'privileges' => [
                        CoEncadrantPrivileges::COENCADRANT_AFFICHER,
                    ],
                ],
                [
                    'controller' => CoEncadrantController::class,
                    'action'     => [
                        'ajouter-co-encadrant',
                        'retirer-co-encadrant',
                    ],
                    'privileges' => [
                        CoEncadrantPrivileges::COENCADRANT_GERER,
                    ],
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
                    'generer-justificatif-coencadrements' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/generer-justificatif-coencadrements/:co-encadrant',
                            'defaults' => [
                                'controller'    => CoEncadrantController::class,
                                'action'        => 'generer-justificatif-coencadrements',
                            ],
                        ],
                    ],
                    'generer-export-csv' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/generer-export-csv/:structure-type/:structure-id',
                            'defaults' => [
                                'controller'    => CoEncadrantController::class,
                                'action'        => 'generer-export-csv',
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
                            'route'    => '/ajouter-co-encadrant/:these',
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

                                'order'    => 300,
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
