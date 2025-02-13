<?php

namespace HDR;

use HDR\Assertion\HDRAssertion;
use HDR\Controller\Factory\HDRSaisieControllerFactory;
use HDR\Controller\HDRSaisieController;
use HDR\Fieldset\Direction\DirectionFieldset;
use HDR\Fieldset\Direction\DirectionFieldsetFactory;
use HDR\Fieldset\Direction\DirectionHydrator;
use HDR\Fieldset\Direction\DirectionHydratorFactory;
use HDR\Fieldset\Generalites\GeneralitesFieldset;
use HDR\Fieldset\Generalites\GeneralitesFieldsetFactory;
use HDR\Fieldset\Generalites\GeneralitesHydrator;
use HDR\Fieldset\Generalites\GeneralitesHydratorFactory;
use HDR\Fieldset\Structures\StructuresFieldset;
use HDR\Fieldset\Structures\StructuresFieldsetFactory;
use HDR\Form\HDRSaisie\HDRSaisieForm;
use HDR\Form\HDRSaisie\HDRSaisieFormFactory;
use HDR\Form\HDRSaisie\HDRSaisieHydrator;
use HDR\Form\HDRSaisie\HDRSaisieHydratorFactory;
use HDR\Provider\Privileges\HDRPrivileges;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            HDRPrivileges::HDR_MODIFICATION_SES_HDRS,
                            HDRPrivileges::HDR_MODIFICATION_TOUTES_HDRS,
                        ],
                        'resources' => ['HDR'],
                        'assertion' => HDRAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => HDRSaisieController::class,
                    'action' => [
                        'ajouter',
                        'modifier',
                        'supprimer',
                        'generalites',
                        'structures',
                        'direction',
                    ],
                    'privileges' => [
                        HDRPrivileges::HDR_MODIFICATION_TOUTES_HDRS,
                        HDRPrivileges::HDR_MODIFICATION_SES_HDRS,
                    ],
                    'assertion' => HDRAssertion::class,
                    'resources' => ['HDR'],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'hdr' => [
                'child_routes' => [
                    'ajouter' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/ajouter',
                            'defaults' => [
                                /** @see HDRSaisieController::ajouterAction() */
                                'controller' => HDRSaisieController::class,
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/modifier/:hdr',
                            'constraints' => [
                                'hdr' => '\d+',
                            ],
                            'defaults' => [
                                /** @see HDRSaisieController::modifierAction() */
                                'controller' => HDRSaisieController::class,
                                'action' => 'modifier',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'generalites' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/generalites',
                                    'defaults' => [
                                        /** @see HDRSaisieController::generalitesAction() */
                                        'action' => 'generalites',
                                    ],
                                ],
                            ],
                            'structures' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/structures',
                                    'defaults' => [
                                        /** @see HDRSaisieController::structuresAction() */
                                        'action' => 'structures',
                                    ],
                                ],
                            ],
                            'direction' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/direction',
                                    'defaults' => [
                                        /** @see HDRSaisieController::directionAction() */
                                        'action' => 'direction',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/supprimer/:hdr',
                            'constraints' => [
                                'hdr' => '\d+',
                            ],
                            'defaults' => [
                                /** @see HDRSaisieController::supprimerAction() */
                                'controller' => HDRSaisieController::class,
                                'action' => 'supprimer',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            HDRSaisieController::class => HDRSaisieControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            HDRSaisieForm::class => HDRSaisieFormFactory::class,
            GeneralitesFieldset::class => GeneralitesFieldsetFactory::class,
            DirectionFieldset::class => DirectionFieldsetFactory::class,
            StructuresFieldset::class => StructuresFieldsetFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            HDRSaisieHydrator::class => HDRSaisieHydratorFactory::class,
            GeneralitesHydrator::class => GeneralitesHydratorFactory::class,
            DirectionHydrator::class => DirectionHydratorFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [],
    ],
    'view_helpers' => [
        'factories' => [],
    ],
    'public_files' => [
        'inline_scripts' => [
        ],
        'stylesheets' => [
        ],
        'head_scripts' => [
        ],
    ],
];