<?php

namespace Soutenance;

use Soutenance\Assertion\PropositionAssertion;
use Soutenance\Assertion\PropositionAssertionFactory;
use Soutenance\Controller\PropositionController;
use Soutenance\Controller\PropositionControllerFactory;
use Soutenance\Controller\PropositionRechercheController;
use Soutenance\Controller\PropositionRechercheControllerFactory;
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
use Soutenance\Service\Proposition\PropositionSearchService;
use Soutenance\Service\Proposition\PropositionSearchServiceFactory;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Proposition\PropositionServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

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
                            PropositionPrivileges::PROPOSITION_MODIFIER_GESTION,
                            PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR,
                            PropositionPrivileges::PROPOSITION_VALIDER_ED,
                            PropositionPrivileges::PROPOSITION_VALIDER_UR,
                            PropositionPrivileges::PROPOSITION_VALIDER_BDD,
                            PropositionPrivileges::PROPOSITION_PRESIDENCE,

                            PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_VALIDER,
                            PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_REVOQUER,
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
                    'controller' => PropositionRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                        'notres',
                        'notresFilters',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VISUALISER,
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
                        'afficher-soutenances-par-ecole-doctorale',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
                        'proposition',
                        'horodatages',
                        'generer-serment',
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
                    'privileges' => [
                        PropositionPrivileges::PROPOSITION_MODIFIER,
                        PropositionPrivileges::PROPOSITION_MODIFIER_GESTION,
                    ],
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
                        'toggle-sursis',
                        'suppression',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_SURSIS,
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
                        'toggle-sursis',
                        'suppression',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_SURSIS,
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
                        'declaration-non-plagiat',
                        'valider-declaration-non-plagiat',
                        'refuser-declaration-non-plagiat',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_VALIDER,
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
                        'revoquer-declaration-non-plagiat',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_REVOQUER,
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'soutenances-par-ecole-doctorale' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/soutenances-par-ecole-doctorale/:ecole',
                    'defaults' => [
                        'controller' => PropositionController::class,
                        'action' => 'afficher-soutenances-par-ecole-doctorale',
                    ],
                ],
            ],
            'soutenance' => [
                'child_routes' => [
                    'proposition' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/proposition',
                            'defaults' => [
                                'controller' => PropositionController::class,
                                'action' => 'proposition',
                            ],
                        ],
                        'child_routes' => [
                            'horodatages' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/horodatages',
                                    'defaults' => [
                                        /** @see PropositionController::horodatagesAction() */
                                        'controller' => PropositionController::class,
                                        'action' => 'horodatages',
                                    ],
                                ],
                            ],
                            'generer-serment' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/generer-serment',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'generer-serment',
                                    ],
                                ],
                            ],
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
                            'declaration-non-plagiat' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/declaration-non-plagiat',
                                    'defaults' => [
                                        'controller' => PropositionController::class,
                                        'action' => 'declaration-non-plagiat',
                                    ],
                                ],
                                'child_routes' => [
                                    'valider' => [
                                        'type' => Literal::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route' => '/valider',
                                            'defaults' => [
                                                'controller' => PropositionController::class,
                                                'action' => 'valider-declaration-non-plagiat',
                                            ],
                                        ],
                                    ],
                                    'refuser' => [
                                        'type' => Literal::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route' => '/refuser',
                                            'defaults' => [
                                                'controller' => PropositionController::class,
                                                'action' => 'refuser-declaration-non-plagiat',
                                            ],
                                        ],
                                    ],
                                    'revoquer' => [
                                        'type' => Literal::class,
                                        'may_terminate' => true,
                                        'options' => [
                                            'route' => '/revoquer',
                                            'defaults' => [
                                                'controller' => PropositionController::class,
                                                'action' => 'revoquer-declaration-non-plagiat',
                                            ],
                                        ],
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
            PropositionSearchService::class => PropositionSearchServiceFactory::class,
            PropositionAssertion::class => PropositionAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            PropositionController::class => PropositionControllerFactory::class,
            PropositionRechercheController::class => PropositionRechercheControllerFactory::class,
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