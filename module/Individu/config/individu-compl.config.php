<?php

namespace Individu;

use Individu\Controller\IndividuComplControllerFactory;
use Individu\Controller\IndividuComplController;
use Individu\Form\IndividuCompl\IndividuComplForm;
use Individu\Form\IndividuCompl\IndividuComplFormFactory;
use Individu\Form\IndividuCompl\IndividuComplHydrator;
use Individu\Form\IndividuCompl\IndividuComplHydratorFactory;
use Individu\Provider\Privilege\IndividuPrivileges;
use Individu\Service\IndividuCompl\IndividuComplService;
use Individu\Service\IndividuCompl\IndividuComplServiceFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndividuComplController::class,
                    'action' => [
                        'index',
                    ],
                    'privilege' => [
                        IndividuPrivileges::INDIVIDU_COMPLMENT_INDEX,
                    ],
                ],
                [
                    'controller' => IndividuComplController::class,
                    'action' => [
                        'afficher',
                    ],
                    'privilege' => [
                        IndividuPrivileges::INDIVIDU_COMPLMENT_AFFICHER,
                    ],
                ],
                [
                    'controller' => IndividuComplController::class,
                    'action' => [
                        'gerer',
                        'ajouter',
                        'modifier',
                        'historiser',
                        'restaurer'
                    ],
                    'privilege' => [
                        IndividuPrivileges::INDIVIDU_COMPLMENT_AFFICHER,
                    ],
                ],
                [
                    'controller' => IndividuComplController::class,
                    'action' => [
                        'detruire',
                    ],
                    'privilege' => [
                        IndividuPrivileges::INDIVIDU_COMPLMENT_SUPPRIMER,
                    ],
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'individu-compl' => [
                                'label' => "Compl. d'individu",
                                'title' => "Compléments d'individu",
                                'route' => 'individu-compl',
                                'resource' => PrivilegeController::getResourceId(IndividuComplController::class, 'index'),
                                'icon' => "fas fa-user-edit",
                                'order' => 65,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'individu-compl' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/individu-compl',
                    'defaults' => [
                        'controller' => IndividuComplController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'afficher' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/afficher/:individu-compl',
                            'defaults' => [
                                /** @see IndividuComplController::afficherAction() */
                                'controller' => IndividuComplController::class,
                                'action' => 'afficher',
                            ],
                        ],
                    ],
                    'ajouter' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/ajouter/:individu',
                            'defaults' => [
                                /** @see IndividuComplController::ajouterAction() */
                                'controller' => IndividuComplController::class,
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/modifier/:individu-compl',
                            'defaults' => [
                                /** @see IndividuComplController::modifierAction() */
                                'controller' => IndividuComplController::class,
                                'action' => 'modifier',
                            ],
                        ],
                    ],
                    'historiser' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/historiser/:individu-compl',
                            'defaults' => [
                                /** @see IndividuComplController::historiserAction() */
                                'controller' => IndividuComplController::class,
                                'action' => 'historiser',
                            ],
                        ],
                    ],
                    'restaurer' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/restaurer/:individu-compl',
                            'defaults' => [
                                /** @see IndividuComplController::restaurerAction() */
                                'controller' => IndividuComplController::class,
                                'action' => 'restaurer',
                            ],
                        ],
                    ],
                    'detruire' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/detruire/:individu-compl',
                            'defaults' => [
                                /** @see IndividuComplController::detruireAction() */
                                'controller' => IndividuComplController::class,
                                'action' => 'detruire',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            IndividuComplController::class => IndividuComplControllerFactory::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
            IndividuComplService::class => IndividuComplServiceFactory::class,
        ]
    ],
    'form_elements' => [
        'factories' => [
            IndividuComplForm::class => IndividuComplFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            IndividuComplHydrator::class => IndividuComplHydratorFactory::class,
        ],
    ],
];