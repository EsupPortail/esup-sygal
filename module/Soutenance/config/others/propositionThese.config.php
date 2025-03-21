<?php

namespace Soutenance;

use Laminas\Router\Http\Segment;
use Soutenance\Assertion\These\PropositionTheseAssertion;
use Soutenance\Controller\These\PropositionThese\PropositionTheseController;
use Soutenance\Controller\These\PropositionThese\PropositionTheseControllerFactory;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseSearchService;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseSearchServiceFactory;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseService;
use Soutenance\Service\Proposition\PropositionThese\PropositionTheseServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
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
                            PropositionPrivileges::PROPOSITION_REVOQUER_STRUCTURE,
                            PropositionPrivileges::PROPOSITION_PRESIDENCE,
                            PropositionPrivileges::PROPOSITION_SUPPRIMER_INFORMATIONS,

                            PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_VALIDER,
                            PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_REVOQUER,
                        ],
                        'resources' => ['These'],
                        'assertion' => PropositionTheseAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => PropositionTheseController::class,
                    'action' => [
                        'afficher-soutenances-par-ecole-doctorale',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => PropositionTheseController::class,
                    'action' => [
                        'proposition',
                        'horodatages',
                        'generer-serment',
                        'generate-view-date-lieu',
                        'generate-view-jury',
                        'generate-view-informations',
                        'generate-view-fichiers',
                        'generate-view-validations-acteurs',
                        'generate-view-validations-structures'
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VISUALISER,
                ],
                [
                    'controller' => PropositionTheseController::class,
                    'action' => [
                        'modifier-date-lieu',
                        'modifier-membre',
                        'effacer-membre',
                        'label-europeen',
                        'anglais',
                        'confidentialite',
                        'changement-titre',
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
                    'controller' => PropositionTheseController::class,
                    'action' => [
                        'supprimer-adresse',
                        'demander-adresse',
                    ],
                    'privileges' => [
                        PropositionPrivileges::PROPOSITION_MODIFIER_GESTION,
                    ],
                ],
                [
                    'controller' => PropositionTheseController::class,
                    'action' => [
                        'valider-acteur',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR,
                ],
                [
                    'controller' => PropositionTheseController::class,
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
                    'controller' => PropositionTheseController::class,
                    'action' => [
                        'revoquer-structure',
                    ],
                    'privileges' => [
                        PropositionPrivileges::PROPOSITION_REVOQUER_STRUCTURE,
                    ],
                ],
                [
                    'controller' => PropositionTheseController::class,
                    'action' => [
                        'signature-presidence',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_PRESIDENCE,
                ],
                [
                    'controller' => PropositionTheseController::class,
                    'action' => [
                        'toggle-sursis',
                        'suppression',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_SURSIS,
                ],
                [
                    'controller' => PropositionTheseController::class,
                    'action' => [
                        'suppression',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_SUPPRIMER_INFORMATIONS,
                ],
                [
                    'controller' => PropositionTheseController::class,
                    'action' => [
                        'declaration-non-plagiat',
                        'valider-declaration-non-plagiat',
                        'refuser-declaration-non-plagiat',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_DECLARATION_HONNEUR_VALIDER,
                ],
                [
                    'controller' => PropositionTheseController::class,
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
                        /** @see PropositionTheseController::afficherSoutenancesParEcoleDoctoraleAction() */
                        'controller' => PropositionTheseController::class,
                        'action' => 'afficher-soutenances-par-ecole-doctorale',
                    ],
                ],
            ],
        ]
    ],

    'service_manager' => [
        'factories' => [
            PropositionTheseService::class => PropositionTheseServiceFactory::class,
            PropositionTheseSearchService::class => PropositionTheseSearchServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            PropositionTheseController::class => PropositionTheseControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
        ],
    ],

    'hydrators' => [
        'invokables' => [
        ],
        'factories' => [
        ],
    ],

    'view_helpers' => [
        'invokables' => [
        ],
    ],
];