<?php

namespace Soutenance;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Soutenance\Assertion\These\PropositionTheseAssertion;
use Soutenance\Assertion\These\PropositionTheseAssertionFactory;
use Soutenance\Controller\HDR\Proposition\PropositionHDRRechercheController;
use Soutenance\Controller\HDR\Proposition\PropositionHDRRechercheControllerFactory;
use Soutenance\Controller\PropositionController;
use Soutenance\Controller\These\PropositionThese\PropositionTheseController;
use Soutenance\Controller\These\PropositionThese\PropositionTheseRechercheController;
use Soutenance\Controller\These\PropositionThese\PropositionTheseRechercheControllerFactory;
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
use Soutenance\Rule\PropositionJuryRule;
use Soutenance\Rule\PropositionJuryRuleFactory;
use Soutenance\Service\Horodatage\HorodatageService;
use Soutenance\Service\Horodatage\HorodatageServiceFactory;
use Soutenance\Service\Proposition\PropositionService;
use Soutenance\Service\Proposition\PropositionServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Acteur' => [],
            ],
        ],
//        'rule_providers' => [
//            PrivilegeRuleProvider::class => [
//                'allow' => [
//                    [
//                        'privileges' => [
//                            PropositionPrivileges::PROPOSITION_VISUALISER,
//                            PropositionPrivileges::PROPOSITION_MODIFIER,
//                            PropositionPrivileges::PROPOSITION_MODIFIER_GESTION,
//                            PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR,
//                            PropositionPrivileges::PROPOSITION_VALIDER_ED,
//                            PropositionPrivileges::PROPOSITION_VALIDER_UR,
//                            PropositionPrivileges::PROPOSITION_VALIDER_BDD,
//                            PropositionPrivileges::PROPOSITION_REVOQUER_STRUCTURE,
//                            PropositionPrivileges::PROPOSITION_PRESIDENCE,
//                            PropositionPrivileges::PROPOSITION_SUPPRIMER_INFORMATIONS,
//
//                            PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_VALIDER,
//                            PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_REVOQUER,
//                        ],
//                        'resources' => ['These'],
//                        'assertion' => PropositionTheseAssertion::class,
//                    ],
//                ],
//            ],
//        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => PropositionTheseRechercheController::class,
                    'action' => [
                        'filters',
                        'notres',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VISUALISER,
                ],
                [
                    'controller' => PropositionHDRRechercheController::class,
                    'action' => [
                        'filters',
                        'notres',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VISUALISER,
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
                        'horodatages',
//                        'generer-serment',
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
//                        'label-europeen',
                        'anglais',
                        'confidentialite',
//                        'changement-titre',
                        'ajouter-adresse',
                        'modifier-adresse',
                        'historiser-adresse',
                        'restaurer-adresse',
                    ],
                    'privileges' => [
                        PropositionPrivileges::PROPOSITION_MODIFIER,
                        PropositionPrivileges::PROPOSITION_MODIFIER_GESTION,
                    ],
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
                        'supprimer-adresse',
                        'demander-adresse',
                    ],
                    'privileges' => [
                        PropositionPrivileges::PROPOSITION_MODIFIER_GESTION,
                    ],
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
//                        'valider-acteur',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR,
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
//                        'valider-structure',
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
//                        'revoquer-structure',
                    ],
                    'privileges' => [
                        PropositionPrivileges::PROPOSITION_REVOQUER_STRUCTURE,
                    ],
                ],
                [
                    'controller' => PropositionController::class,
                    'action' => [
//                        'signature-presidence',
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
                        'suppression',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_SUPPRIMER_INFORMATIONS,
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
            'soutenance_these' => [
                'child_routes' => $soutenanceChildRoutes = [
                    'proposition' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/proposition',
                            'defaults' => [
                                /** @see PropositionTheseController::propositionAction() */
                                /** @see PropositionHDRController::propositionAction() */
                                'controller' => PropositionController::class,
                                'action' => 'proposition',
                            ],
                        ],
                        'child_routes' => [
                            'ajouter-adresse' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/ajouter-adresse/:proposition',
                                    'defaults' => [
                                        /** @see PropositionController::ajouterAdresseAction() */
                                        'controller' => PropositionController::class,
                                        'action' => 'ajouter-adresse',
                                    ],
                                ],
                            ],
                            'modifier-adresse' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/modifier-adresse/:adresse',
                                    'defaults' => [
                                        /** @see PropositionController::modifierAdresseAction() */
                                        'controller' => PropositionController::class,
                                        'action' => 'modifier-adresse',
                                    ],
                                ],
                            ],
                            'historiser-adresse' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/historiser-adresse/:adresse',
                                    'defaults' => [
                                        /** @see PropositionController::historiserAdresseAction() */
                                        'controller' => PropositionController::class,
                                        'action' => 'historiser-adresse',
                                    ],
                                ],
                            ],
                            'restaurer-adresse' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/restaurer-adresse/:adresse',
                                    'defaults' => [
                                        /** @see PropositionController::restaurerAdresseAction() */
                                        'controller' => PropositionController::class,
                                        'action' => 'restaurer-adresse',
                                    ],
                                ],
                            ],
                            'supprimer-adresse' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/supprimer-adresse/:adresse',
                                    'defaults' => [
                                        /** @see PropositionController::supprimerAdresseAction() */
                                        'controller' => PropositionController::class,
                                        'action' => 'supprimer-adresse',
                                    ],
                                ],
                            ],
                            'demander-adresse' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/demander-adresse/:proposition',
                                    'defaults' => [
                                        /** @see PropositionController::demanderAdresseAction() */
                                        'controller' => PropositionController::class,
                                        'action' => 'demander-adresse',
                                    ],
                                ],
                            ],
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
                                        /** @see PropositionTheseController::genererSermentAction() */
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
                                        /** @see PropositionController::generateViewDateLieuAction() */
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
                                        /** @see PropositionController::generateViewJuryAction() */
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
                                        /** @see PropositionController::generateViewInformationsAction() */
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
                                        /** @see PropositionController::toggleSursisAction() */
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
                                        /** @see PropositionController::suppressionAction() */
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
                                        /** @see PropositionController::modifierDateLieuAction() */
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
                                        /** @see PropositionController::modifierMembreAction() */
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
                                        /** @see PropositionController::effacerMembreAction() */
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
                                        /** @see PropositionTheseController::labelEuropeenAction() */
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
                                        /** @see PropositionController::anglaisAction() */
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
                                        /** @see PropositionController::confidentialiteAction() */
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
                                        /** @see PropositionTheseController::changementTitreAction() */
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
                                        /** @see PropositionTheseController::validerActeurAction() */
                                        /** @see PropositionHDRController::validerActeurAction() */
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
                                        /** @see PropositionTheseController::validerStructureAction() */
                                        /** @see PropositionHDRController::validerStructureAction() */
                                        'controller' => PropositionController::class,
                                        'action' => 'valider-structure',
                                    ],
                                ],
                            ],
                            'revoquer-structure' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/revoquer-structure',
                                    'defaults' => [
                                        /** @see PropositionTheseController::revoquerStructureAction() */
                                        /** @see PropositionHDRController::revoquerStructureAction() */
                                        'controller' => PropositionController::class,
                                        'action' => 'revoquer-structure',
                                    ],
                                ],
                            ],
                            'refuser-structure' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/refuser-PropositionController',
                                    'defaults' => [
                                        /** @see PropositionController::refuserStructureAction() */
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
                                        /** @see PropositionTheseController::signaturePresidenceAction() */
                                        /** @see PropositionHDRController::signaturePresidenceAction() */
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
                                        /** @see PropositionTheseController::declarationNonPlagiatAction() */
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
                                                /** @see PropositionTheseController::validerDeclarationNonPlagiatAction() */
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
                                                /** @see PropositionTheseController::refuserDeclarationNonPlagiatAction() */
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
                                                /** @see PropositionTheseController::revoquerDeclarationNonPlagiatAction() */
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
            'soutenance_hdr' => [
                'child_routes' => $soutenanceChildRoutes,
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            PropositionService::class => PropositionServiceFactory::class,
            PropositionTheseAssertion::class => PropositionTheseAssertionFactory::class,
            HorodatageService::class => HorodatageServiceFactory::class,
            PropositionJuryRule::class => PropositionJuryRuleFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            PropositionTheseRechercheController::class => PropositionTheseRechercheControllerFactory::class,
            PropositionHDRRechercheController::class => PropositionHDRRechercheControllerFactory::class,
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