<?php

use ComiteSuivi\Controller\CompteRenduController;
use ComiteSuivi\Controller\CompteRenduControllerFactory;
use ComiteSuivi\Form\CompteRendu\CompteRenduForm;
use ComiteSuivi\Form\CompteRendu\CompteRenduFormFactory;
use ComiteSuivi\Form\CompteRendu\CompteRenduHydrator;
use ComiteSuivi\Form\CompteRendu\CompteRenduHydratorFactory;
use ComiteSuivi\Service\CompteRendu\CompteRenduService;
use ComiteSuivi\Service\CompteRendu\CompteRenduServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => CompteRenduController::class,
                    'action'     => [
                        'afficher',
                        'ajouter',
                        'modifier',
                        'historiser',
                        'restaurer',
                        'supprimer',
                        'finaliser',
                    ],
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'compte-rendu' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/compte-rendu',
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'ajouter' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/ajouter/:comite-suivi[/:membre]',
                            'defaults' => [
                                'controller'    => CompteRenduController::class,
                                'action'        => 'ajouter',
                            ],
                        ],

                    ],
                    'afficher' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/afficher/:compte-rendu',
                            'defaults' => [
                                'controller'    => CompteRenduController::class,
                                'action'        => 'afficher',
                            ],
                        ],
                    ],
                    'historiser' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/historiser/:compte-rendu',
                            'defaults' => [
                                'controller'    => CompteRenduController::class,
                                'action'        => 'historiser',
                            ],
                        ],
                    ],
                    'restaurer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/restaurer/:compte-rendu',
                            'defaults' => [
                                'controller'    => CompteRenduController::class,
                                'action'        => 'restaurer',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/modifier/:compte-rendu',
                            'defaults' => [
                                'controller'    => CompteRenduController::class,
                                'action'        => 'modifier',
                            ],
                        ],
                    ],
                    'finaliser' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/finaliser/:compte-rendu',
                            'defaults' => [
                                'controller'    => CompteRenduController::class,
                                'action'        => 'finaliser',
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/supprimer/:compte-rendu',
                            'defaults' => [
                                'controller'    => CompteRenduController::class,
                                'action'        => 'supprimer',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],


    'service_manager' => [
        'factories' => [
            CompteRenduService::class =>  CompteRenduServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            CompteRenduController::class => CompteRenduControllerFactory::class,
        ],
    ],
    'form_elements'   => [
        'factories' => [
            CompteRenduForm::class => CompteRenduFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            CompteRenduHydrator::class => CompteRenduHydratorFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
        ],
        'factories' => [],
    ],
];
