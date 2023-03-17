<?php

namespace Individu;

use Individu\Controller\IndividuComplController;
use Individu\Controller\IndividuComplControllerFactory;
use Individu\Form\IndividuCompl\IndividuComplForm;
use Individu\Form\IndividuCompl\IndividuComplFormFactory;
use Individu\Form\IndividuCompl\IndividuComplHydrator;
use Individu\Form\IndividuCompl\IndividuComplHydratorFactory;
use Individu\Provider\Privilege\IndividuPrivileges;
use Individu\Service\IndividuCompl\IndividuComplService;
use Individu\Service\IndividuCompl\IndividuComplServiceFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;

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

    'router' => [
        'routes' => [
            'individu-compl' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/individu-compl',
                    'defaults' => [
                        'controller' => IndividuComplController::class,
                    ],
                ],
                'may_terminate' => false,
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
                    'gerer' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/gerer/:individu',
                            'defaults' => [
                                /** @see IndividuComplController::gererAction() */
                                'action' => 'gerer',
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