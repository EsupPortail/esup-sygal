<?php

namespace Soutenance;

use Soutenance\Assertion\PropositionAssertion;
use Soutenance\Assertion\PropositionAssertionFactory;
use Soutenance\Controller\PropositionController;
use Soutenance\Controller\PropositionControllerFactory;
use Soutenance\Form\Anglais\AnglaisForm;
use Soutenance\Form\Anglais\AnglaisFormFactory;
use Soutenance\Form\Anglais\AnglaisHydrator;
use Soutenance\Form\ChangementTitre\ChangementTitreForm;
use Soutenance\Form\ChangementTitre\ChangementTitreFormFactory;
use Soutenance\Form\ChangementTitre\ChangementTitreHydrator;
use Soutenance\Form\Confidentialite\ConfidentialiteForm;
use Soutenance\Form\Confidentialite\ConfidentialiteFormFactory;
use Soutenance\Form\Confidentialite\ConfidentialiteHydrator;
use Soutenance\Form\DateLieu\DateLieuForm;
use Soutenance\Form\DateLieu\DateLieuFormFactory;
use Soutenance\Form\DateLieu\DateLieuHydrator;
use Soutenance\Form\LabelEuropeen\LabelEuropeenForm;
use Soutenance\Form\LabelEuropeen\LabelEuropeenFormFactory;
use Soutenance\Form\LabelEuropeen\LabelEuropeenHydrator;
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
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Acteur' => [],
            ],
        ],
        'rule_providers' => [
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
                        'resources' => ['These'],
                        'assertion' => PropositionAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => PropositionController::class,
                    'action' => [
                        'proposition',
                        'generate-view-date-lieu',
                        'generate-view-jury',
                        'generate-view-informations',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VISUALISER,
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
                        'modifier-date-lieu',
                        'modifier-membre',
                        'effacer-membre',
                        'label-europeen',
                        'anglais',
                        'confidentialite',
                        'changement-titre',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_MODIFIER,
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
                        'valider-acteur',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR,
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
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
                    'action' => [
                        'signature-presidence',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_PRESIDENCE,
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
                        'avancement',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VISUALISER,
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
                        'toggle-sursis',
                        'suppression',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_SURSIS,
                ],


            ],
        ],
    ],

    'router' => [
        'routes' => [
            'soutenance' => [
                'child_routes' => [
                    'avancement' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/avancement/:these',
                            'defaults' => [
                                'controller' => PropositionController::class,
                                'action' => 'avancement',
                            ],
                        ],
                    ],
                    'proposition' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/proposition/:these',
                            'defaults' => [
                                'controller' => PropositionController::class,
                                'action' => 'proposition',
                            ],
                        ],
                        'child_routes' => [
                            'generate-view-date-lieu' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/generate-view-date-lieu',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'generate-view-date-lieu',
                                    ],
                                ],
                            ],
                            'generate-view-jury' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/generate-view-jury',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'generate-view-jury',
                                    ],
                                ],
                            ],
                            'generate-view-informations' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/generate-view-informations',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'generate-view-informations',
                                    ],
                                ],
                            ],
                            'sursis' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/sursis',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'toggle-sursis',
                                    ],
                                ],
                            ],
                            'suppression' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/suppression',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'suppression',
                                    ],
                                ],
                            ],
                            'modifier-date-lieu' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/modifier-date-lieu',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'modifier-date-lieu',
                                    ],
                                ],
                            ],
                            'modifier-membre' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/modifier-membre[/:membre]',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'modifier-membre',
                                    ],
                                ],
                            ],
                            'effacer-membre' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/effacer-membre/:membre',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'effacer-membre',
                                    ],
                                ],
                            ],
                            'label-europeen' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/label-europeen',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'label-europeen',
                                    ],
                                ],
                            ],
                            'anglais' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/anglais',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'anglais',
                                    ],
                                ],
                            ],
                            'confidentialite' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/confidentialite',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'confidentialite',
                                    ],
                                ],
                            ],
                            'changement-titre' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/changement-titre',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'changement-titre',
                                    ],
                                ],
                            ],
                            'valider' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/valider',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'valider-acteur',
                                    ],
                                ],
                            ],
                            'valider-structure' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/valider-structure',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'valider-structure',
                                    ],
                                ],
                            ],
                            'refuser-structure' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/refuser-PropositionController',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'refuser-structure',
                                    ],
                                ],
                            ],
                            'signature-presidence' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/signature-presidence',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'signature-presidence',
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
            AnglaisForm::class => AnglaisFormFactory::class,
            ChangementTitreForm::class => ChangementTitreFormFactory::class,
            DateLieuForm::class => DateLieuFormFactory::class,
            MembreForm::class => MembreFormFactory::class,
            LabelEuropeenForm::class => LabelEuropeenFormFactory::class,
            ConfidentialiteForm::class => ConfidentialiteFormFactory::class,
            RefusForm::class => RefusFormFactory::class,
        ],
    ],

    'hydrators' => [
        'invokables' => [
            AnglaisHydrator::class => AnglaisHydrator::class,
            DateLieuHydrator::class => DateLieuHydrator::class,
            LabelEuropeenHydrator::class => LabelEuropeenHydrator::class,
            ChangementTitreHydrator::class => ChangementTitreHydrator::class,
            ConfidentialiteHydrator::class => ConfidentialiteHydrator::class,
        ],
        'factories' => [
            MembreHydrator::class => MembreHydratorFactory::class,
        ],
    ],

    'view_helpers' => [
        'invokables' => [
        ],
    ],
];