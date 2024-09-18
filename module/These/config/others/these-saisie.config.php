<?php

namespace These;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use These\Assertion\These\TheseAssertion;
use These\Controller\Factory\TheseSaisieControllerFactory;
use These\Controller\TheseRechercheController;
use These\Controller\TheseSaisieController;
use These\Fieldset\Direction\DirectionFieldset;
use These\Fieldset\Direction\DirectionFieldsetFactory;
use These\Fieldset\Direction\DirectionHydrator;
use These\Fieldset\Direction\DirectionHydratorFactory;
use These\Fieldset\Encadrement\EncadrementFieldset;
use These\Fieldset\Encadrement\EncadrementFieldsetFactory;
use These\Fieldset\Encadrement\EncadrementHydrator;
use These\Fieldset\Encadrement\EncadrementHydratorFactory;
use These\Fieldset\Financement\FinancementFieldset;
use These\Fieldset\Financement\FinancementFieldsetFactory;
use These\Fieldset\Financement\FinancementHydrator;
use These\Fieldset\Financement\FinancementHydratorFactory;
use These\Fieldset\Generalites\GeneralitesFieldset;
use These\Fieldset\Generalites\GeneralitesFieldsetFactory;
use These\Fieldset\Generalites\GeneralitesHydrator;
use These\Fieldset\Generalites\GeneralitesHydratorFactory;
use These\Fieldset\Structures\StructuresFieldset;
use These\Fieldset\Structures\StructuresFieldsetFactory;
use These\Fieldset\TitreAcces\TitreAccesFieldset;
use These\Fieldset\TitreAcces\TitreAccesFieldsetFactory;
use These\Form\Financement\FinancementsForm;
use These\Form\Financement\FinancementsFormFactory;
use These\Form\TheseSaisie\TheseSaisieForm;
use These\Form\TheseSaisie\TheseSaisieFormFactory;
use These\Form\TheseSaisie\TheseSaisieHydrator;
use These\Form\TheseSaisie\TheseSaisieHydratorFactory;
use These\Provider\Privilege\ThesePrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            ThesePrivileges::THESE_MODIFICATION_TOUTES_THESES,
                            ThesePrivileges::THESE_MODIFICATION_SES_THESES,
                        ],
                        'resources' => ['These'],
                        'assertion' => TheseAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => TheseSaisieController::class,
                    'action' => [
                        'ajouter',
                        'modifier',
                        'supprimer',
                        'generalites',
                        'structures',
                        'financements'
                    ],
                    'privileges' => [
                        ThesePrivileges::THESE_MODIFICATION_TOUTES_THESES,
                        ThesePrivileges::THESE_MODIFICATION_SES_THESES,
                    ],
                    'assertion' => TheseAssertion::class,
                    'resources' => ['These'],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'these' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/these',
                    'defaults' => [
                        'controller' => TheseRechercheController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'ajouter' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/ajouter',
                            'defaults' => [
                                /** @see TheseSaisieController::ajouterAction() */
                                'controller' => TheseSaisieController::class,
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/modifier/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                /** @see TheseSaisieController::modifierAction() */
                                'controller' => TheseSaisieController::class,
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
                                        /** @see TheseSaisieController::generalitesAction() */
                                        'action' => 'generalites',
                                    ],
                                ],
                            ],
                            'structures' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/structures',
                                    'defaults' => [
                                        /** @see TheseSaisieController::structuresAction() */
                                        'action' => 'structures',
                                    ],
                                ],
                            ],
                            'financements' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/financements',
                                    'defaults' => [
                                        /** @see TheseSaisieController::financementsAction() */
                                        'action' => 'financements',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/supprimer/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                /** @see TheseSaisieController::supprimerAction() */
                                'controller' => TheseSaisieController::class,
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
            TheseSaisieController::class => TheseSaisieControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            TheseSaisieForm::class => TheseSaisieFormFactory::class,
            EncadrementFieldset::class => EncadrementFieldsetFactory::class,
            GeneralitesFieldset::class => GeneralitesFieldsetFactory::class,
            DirectionFieldset::class => DirectionFieldsetFactory::class,
            StructuresFieldset::class => StructuresFieldsetFactory::class,
            FinancementFieldset::class => FinancementFieldsetFactory::class,
            TitreAccesFieldset::class => TitreAccesFieldsetFactory::class,
            FinancementsForm::class => FinancementsFormFactory::class
        ],
    ],
    'hydrators' => [
        'factories' => [
            TheseSaisieHydrator::class => TheseSaisieHydratorFactory::class,

            EncadrementHydrator::class => EncadrementHydratorFactory::class,
            GeneralitesHydrator::class => GeneralitesHydratorFactory::class,
            DirectionHydrator::class => DirectionHydratorFactory::class,
            FinancementHydrator::class => FinancementHydratorFactory::class,
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
            '090_these' => "/js/these.js",
        ],
    ],
];