<?php

namespace Formation;

use Formation\Controller\FormationController;
use Formation\Controller\FormationControllerFactory;
use Formation\Form\Formation\FormationForm;
use Formation\Form\Formation\FormationFormFactory;
use Formation\Form\Formation\FormationHydrator;
use Formation\Form\Formation\FormationHydratorFactory;
use Formation\Provider\Privilege\FormationPrivileges;
use Formation\Service\Formation\FormationService;
use Formation\Service\Formation\FormationServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => FormationController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_INDEX,
                    ],
                ],
                [
                    'controller' => FormationController::class,
                    'action' => [
                        'afficher',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_AFFICHER,
                    ],
                ],
                [
                    'controller' => FormationController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_AJOUTER,
                    ],
                ],
                [
                    'controller' => FormationController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_MODIFIER,
                    ],
                ],
                [
                    'controller' => FormationController::class,
                    'action' => [
                        'historiser',
                        'restaurer',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_HISTORISER,
                    ],
                ],
                [
                    'controller' => FormationController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => [
                        FormationPrivileges::FORMATION_SUPPRIMER,
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
                            'formation' => [
                                'label'    => 'Formations',
                                'route'    => 'formation/formation',
                                'resource' => PrivilegeController::getResourceId(FormationController::class, 'index') ,
                                'order'    => 90,
                                'pages' => [
                                    'afficher' => [
                                        'label'    => "Affichage d'une formation",
                                        'route'    => 'formation/formation/afficher',
                                        'resource' => PrivilegeController::getResourceId(FormationController::class, 'afficher') ,
                                        'order'    => 100,
                                        'visible' => false,
                                    ],
                                ],
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
                    'formation' => [
                        'type'  => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/formation',
                            'defaults' => [
                                'controller' => FormationController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'afficher' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/afficher/:formation',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        'action'     => 'afficher',
                                    ],
                                ],
                            ],
                            'ajouter' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter[/:module]',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        'action'     => 'ajouter',
                                    ],
                                ],
                            ],
                            'modifier' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/modifier/:formation',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        'action'     => 'modifier',
                                    ],
                                ],
                            ],
                            'historiser' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/historiser/:formation',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        'action'     => 'historiser',
                                    ],
                                ],
                            ],
                            'restaurer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/restaurer/:formation',
                                    'defaults' => [
                                        'controller' => FormationController::class,
                                        'action'     => 'restaurer',
                                    ],
                                ],
                            ],
                            'supprimer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/supprimer/:formation',
                                    'defaults' => [
                                        'controller' => FormationController::class,
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
            FormationService::class => FormationServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            FormationController::class => FormationControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            FormationForm::class => FormationFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            FormationHydrator::class => FormationHydratorFactory::class,
        ],
    ]

];