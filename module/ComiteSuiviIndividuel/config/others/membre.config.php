<?php

namespace Soutenance;

use Application\Provider\Privilege\RapportPrivileges;
use ComiteSuiviIndividuel\Controller\MembreController;
use ComiteSuiviIndividuel\Controller\MembreControllerFactory;
use ComiteSuiviIndividuel\Form\Membre\MembreForm;
use ComiteSuiviIndividuel\Form\Membre\MembreFormFactory;
use ComiteSuiviIndividuel\Form\Membre\MembreHydrator;
use ComiteSuiviIndividuel\Form\Membre\MembreHydratorFactory;
use ComiteSuiviIndividuel\Service\Membre\MembreService;
use ComiteSuiviIndividuel\Service\Membre\MembreServiceFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
//                'Acteur' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
//                'allow' => [
//                    [
//                        'privileges' => [
//                            InterventionPrivileges::INTERVENTION_AFFICHER,
//                            InterventionPrivileges::INTERVENTION_MODIFIER,
//                        ],
//                        'resources' => ['These'],
//                        'assertion' => InterventionAssertion::class,
//                    ],
//                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => MembreController::class,
                    'action' => [
                        'ajouter',
                        'modifier',
                        'supprimer',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_CSI_TELEVERSER_SIEN,
                        RapportPrivileges::RAPPORT_CSI_TELEVERSER_TOUT,
                    ],
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'comite-suivi-individuel' => [
                'type' => Literal::class,
                'may_terminate' => false,
                'options' => [
                    'route' => '/comite-suivi-individuel',
                    'defaults' => [
                        'controller' => MembreController::class,
                        'action' => 'ajouter',
                    ],
                ],
                'child_routes' => [
                    'membre' => [
                        'type' => Literal::class,
                        'may_terminate' => false,
                        'options' => [
                            'route' => '/membre',
                            'defaults' => [
                                'controller' => MembreController::class,
                                'action' => 'ajouter',
                            ],
                        ],
                        'child_routes' => [
                            'ajouter' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/ajouter/:these',
                                    'defaults' => [
                                        'action' => 'ajouter',
                                    ],
                                ],
                            ],
                            'modifier' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/modifier/:membre',
                                    'defaults' => [
                                        'action' => 'modifier',
                                    ],
                                ],
                            ],
                            'supprimer' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/supprimer/:membre',
                                    'defaults' => [
                                        'action' => 'supprimer',
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
            MembreService::class => MembreServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            MembreController::class => MembreControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            MembreForm::class => MembreFormFactory::class,
        ],
    ],

    'hydrators' => [
        'factories' => [
            MembreHydrator::class => MembreHydratorFactory::class,
        ],
    ],

    'view_helpers' => [
        'invokables' => [
        ],
    ],
];