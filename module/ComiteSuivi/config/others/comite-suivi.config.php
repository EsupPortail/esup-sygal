<?php

use Application\Provider\Privilege\StructurePrivileges;
use Application\Provider\Privilege\SubstitutionPrivileges;
use ComiteSuivi\Controller\ComiteSuiviController;
use ComiteSuivi\Controller\ComiteSuiviControllerFactory;
use ComiteSuivi\Form\ComiteSuivi\ComiteSuiviForm;
use ComiteSuivi\Form\ComiteSuivi\ComiteSuiviFormFactory;
use ComiteSuivi\Form\ComiteSuivi\ComiteSuiviHydrator;
use ComiteSuivi\Form\ComiteSuivi\ComiteSuiviHydratorFactory;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviService;
use ComiteSuivi\Service\ComiteSuivi\ComiteSuiviServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ComiteSuiviController::class,
                    'action'     => [
                        'index',
                        'ajouter',
                        'supprimer',
                        'historiser',
                        'restaurer',
                        'afficher',
                        'modifier',
                    ],
                    'privileges' => [
                        StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES,
                    ],
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'comite-suivi' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/comite-suivi[/:these]',
                    'defaults' => [
                        'controller'    => ComiteSuiviController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'ajouter' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/ajouter',
                            'defaults' => [
                                'controller'    => ComiteSuiviController::class,
                                'action'        => 'ajouter',
                            ],
                        ],
                    ],
                    'afficher' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/afficher/:comite-suivi',
                            'defaults' => [
                                'controller'    => ComiteSuiviController::class,
                                'action'        => 'afficher',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/modifier/:comite-suivi',
                            'defaults' => [
                                'controller'    => ComiteSuiviController::class,
                                'action'        => 'modifier',
                            ],
                        ],
                    ],
                    'historiser' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/historiser/:comite-suivi',
                            'defaults' => [
                                'controller'    => ComiteSuiviController::class,
                                'action'        => 'historiser',
                            ],
                        ],
                    ],
                    'restaurer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/restaurer/:comite-suivi',
                            'defaults' => [
                                'controller'    => ComiteSuiviController::class,
                                'action'        => 'restaurer',
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/supprimer/:comite-suivi',
                            'defaults' => [
                                'controller'    => ComiteSuiviController::class,
                                'action'        => 'supprimer',
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
                    'comite-suivi' => [
                        'label'    => 'Comité de suivi',
                        'route'    => 'comite-suivi',
                        'resource' => SubstitutionPrivileges::getResourceId(SubstitutionPrivileges::SUBSTITUTION_CONSULTATION_TOUTES_STRUCTURES),
                        'order'    => 50,
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            ComiteSuiviService::class => ComiteSuiviServiceFactory::class
        ],
    ],
    'controllers'     => [
        'factories' => [
            ComiteSuiviController::class => ComiteSuiviControllerFactory::class,
        ],
    ],
    'form_elements'   => [
        'factories' => [
            ComiteSuiviForm::class => ComiteSuiviFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            ComiteSuiviHydrator::class => ComiteSuiviHydratorFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [],
        'factories' => [],
    ],
];
