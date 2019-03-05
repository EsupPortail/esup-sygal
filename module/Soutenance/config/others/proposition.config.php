<?php

namespace Soutenance;
use Soutenance\Assertion\PropositionAssertion;
use Soutenance\Assertion\PropositionAssertionFactory;
use Soutenance\Controller\Proposition\PropositionController;
use Soutenance\Controller\Proposition\PropositionControllerFactory;
use Soutenance\Controller\SoutenanceController;
use Soutenance\Form\Confidentialite\ConfidentialiteForm;
use Soutenance\Form\Confidentialite\ConfidentialiteFormFactory;
use Soutenance\Form\Confidentialite\ConfidentialiteHydrator;
use Soutenance\Form\DateLieu\DateLieuForm;
use Soutenance\Form\DateLieu\DateLieuFormFactory;
use Soutenance\Form\DateLieu\DateLieuHydrator;
use Soutenance\Form\LabelEtAnglais\LabelEtAnglaisForm;
use Soutenance\Form\LabelEtAnglais\LabelEtAnglaisFormFactory;
use Soutenance\Form\LabelEtAnglais\LabelEtAnglaisHydrator;
use Soutenance\Form\Membre\MembreForm;
use Soutenance\Form\Membre\MembreFormFactory;
use Soutenance\Form\Membre\MembreHydrator;
use Soutenance\Form\Membre\MembreHydratorFactory;
use Soutenance\Form\Refus\RefusForm;
use Soutenance\Form\Refus\RefusFormFactory;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Proposition\PropositionServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Acteur' => [],
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            PropositionPrivileges::PROPOSITION_VISUALISER,
                            PropositionPrivileges::PROPOSITION_MODIFIER,
                            PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR,
                            PropositionPrivileges::PROPOSITION_VALIDER_ED,
                            PropositionPrivileges::PROPOSITION_VALIDER_UR,
                            PropositionPrivileges::PROPOSITION_VALIDER_BDD,
                            PropositionPrivileges::PROPOSITION_PRESIDENCE,
                        ],
                        'resources'  => ['These'],
                        'assertion'  => PropositionAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => PropositionController::class,
                    'action'     => [
                        'proposition',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VISUALISER,
                ],
                [
                    'controller' => PropositionController::class,
                    'action'     => [
                        'modifier-date-lieu',
                        'modifier-membre',
                        'effacer-membre',
                        'label-et-anglais',
                        'confidentialite',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_MODIFIER,
                ],
                [
                    'controller' => PropositionController::class,
                    'action'     => [
                        'valider-acteur',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR,
                ],
                [
                    'controller' => PropositionController::class,
                    'action'     => [
                        'valider-structure',
                        'refuser-structure',
                    ],
                    'privileges' => [
                        PropositionPrivileges::PROPOSITION_VALIDER_UR,
                        PropositionPrivileges::PROPOSITION_VALIDER_ED,
                        PropositionPrivileges::PROPOSITION_VALIDER_BDD,
                    ],
                ],
                [
                    'controller' => PropositionController::class,
                    'action'     => [
                        'signature-presidence',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_PRESIDENCE,
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'soutenance' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/soutenance[/:these]',
                    'defaults' => [
                        'controller' => SoutenanceController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes' => [
                    'proposition' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/proposition',
                            'defaults' => [
                                'controller' => PropositionController::class,
                                'action'     => 'proposition',
                            ],
                        ],
                        'child_routes' => [
                            'modifier-date-lieu' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/modifier-date-lieu',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action'     => 'modifier-date-lieu',
                                    ],
                                ],
                            ],
                            'modifier-membre' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/modifier-membre[/:membre]',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action'     => 'modifier-membre',
                                    ],
                                ],
                            ],
                            'effacer-membre' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/effacer-membre/:membre',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action'     => 'effacer-membre',
                                    ],
                                ],
                            ],
                            'label-et-anglais' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/label-et-anglais',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action'     => 'label-et-anglais',
                                    ],
                                ],
                            ],
                            'confidentialite' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/confidentialite',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action'     => 'confidentialite',
                                    ],
                                ],
                            ],
                            'valider' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/valider',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action'     => 'valider-acteur',
                                    ],
                                ],
                            ],
                            'valider-structure' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/valider-structure',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action'     => 'valider-structure',
                                    ],
                                ],
                            ],
                            'refuser-structure' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/refuser-PropositionController',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action'     => 'refuser-structure',
                                    ],
                                ],
                            ],
                            'signature-presidence' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/signature-presidence',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action'     => 'signature-presidence',
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
            PropositionService::class => PropositionServiceFactory::class,
            PropositionAssertion::class => PropositionAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            PropositionController::class => PropositionControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            DateLieuForm::class => DateLieuFormFactory::class,
            MembreForm::class => MembreFormFactory::class,
            LabelEtAnglaisForm::class => LabelEtAnglaisFormFactory::class,
            ConfidentialiteForm::class => ConfidentialiteFormFactory::class,
            RefusForm::class => RefusFormFactory::class,


        ],
    ],

    'hydrators' => [
        'invokables' => [
            DateLieuHydrator::class => DateLieuHydrator::class,
            LabelEtAnglaisHydrator::class => LabelEtAnglaisHydrator::class,
            ConfidentialiteHydrator::class => ConfidentialiteHydrator::class,
        ],
        'factories' => [
            MembreHydrator::class => MembreHydratorFactory::class,
        ],
    ],
];