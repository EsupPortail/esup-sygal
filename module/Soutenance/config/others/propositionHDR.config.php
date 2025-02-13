<?php

namespace Soutenance;

use Soutenance\Assertion\HDR\PropositionHDRAssertion;
use Soutenance\Assertion\HDR\PropositionHDRAssertionFactory;
use Soutenance\Controller\HDR\Proposition\PropositionHDRController;
use Soutenance\Controller\HDR\Proposition\PropositionHDRControllerFactory;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRSearchService;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRSearchServiceFactory;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRService;
use Soutenance\Service\Proposition\PropositionHDR\PropositionHDRServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'HDR' => [],
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
                            PropositionPrivileges::PROPOSITION_VALIDER_UR,
                            PropositionPrivileges::PROPOSITION_VALIDER_BDD,
                            PropositionPrivileges::PROPOSITION_REVOQUER_STRUCTURE,
                            PropositionPrivileges::PROPOSITION_PRESIDENCE,
                            PropositionPrivileges::PROPOSITION_SUPPRIMER_INFORMATIONS,
                        ],
                        'resources' => ['HDR'],
                        'assertion' => PropositionHDRAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => PropositionHDRController::class,
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
                    'controller' => PropositionHDRController::class,
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
                    'controller' => PropositionHDRController::class,
                    'action' => [
                        'supprimer-adresse',
                        'demander-adresse',
                    ],
                    'privileges' => [
                        PropositionPrivileges::PROPOSITION_MODIFIER_GESTION,
                    ],
                ],
                [
                    'controller' => PropositionHDRController::class,
                    'action' => [
                        'valider-acteur',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VALIDER_ACTEUR,
                ],
                [
                    'controller' => PropositionHDRController::class,
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
                    'controller' => PropositionHDRController::class,
                    'action' => [
                        'revoquer-structure',
                    ],
                    'privileges' => [
                        PropositionPrivileges::PROPOSITION_REVOQUER_STRUCTURE,
                    ],
                ],
                [
                    'controller' => PropositionHDRController::class,
                    'action' => [
                        'signature-presidence',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_PRESIDENCE,
                ],
                [
                    'controller' => PropositionHDRController::class,
                    'action' => [
                        'toggle-sursis',
                        'suppression',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_SURSIS,
                ],
                [
                    'controller' => PropositionHDRController::class,
                    'action' => [
                        'suppression',
                    ],
                    'privileges' => PropositionPrivileges::PROPOSITION_SUPPRIMER_INFORMATIONS,
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            PropositionHDRService::class => PropositionHDRServiceFactory::class,
            PropositionHDRSearchService::class => PropositionHDRSearchServiceFactory::class,
            PropositionHDRAssertion::class => PropositionHDRAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            PropositionHDRController::class => PropositionHDRControllerFactory::class,
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