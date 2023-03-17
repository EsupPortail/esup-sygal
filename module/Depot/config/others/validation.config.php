<?php

use Depot\Assertion\These\TheseAssertion;
use Depot\Controller\Factory\ValidationControllerFactory;
use Depot\Controller\ValidationController;
use Depot\Provider\Privilege\DepotPrivileges;
use Depot\Provider\Privilege\ValidationPrivileges;
use Depot\Service\Validation\DepotValidationService;
use Depot\Service\Validation\DepotValidationServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize'    => [
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            ValidationPrivileges::THESE_VALIDATION_RDV_BU,
                            ValidationPrivileges::THESE_VALIDATION_RDV_BU_SUPPR,
                            ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE,
                            ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE_SUPPR,
                            ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE,
                            ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE_SUPPR,
                            ValidationPrivileges::VALIDATION_CORRECTION_THESE,
                            ValidationPrivileges::VALIDATION_CORRECTION_THESE_SUPPR,
                            ValidationPrivileges::VALIDATION_VERSION_PAPIER_CORRIGEE,
                        ],
                        'resources'  => ['These'],
                        'assertion'  => TheseAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ValidationController::class,
                    'action'     => [
                        'page-de-couverture',
                    ],
                    'privileges' => ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE,
//                    'assertion'  => \These\Assertion\These\TheseAssertion::class,
                ],
                [
                    'controller' => ValidationController::class,
                    'action'     => [
                        'rdv-bu',
                    ],
                    'privileges' => ValidationPrivileges::THESE_VALIDATION_RDV_BU,
                    'assertion'  => TheseAssertion::class,
                ],
                [
                    'controller' => ValidationController::class,
                    'action'     => [
                        'validation-depot-these-corrigee',
                        'validation-correction-these',
                    ],
                    'privileges' => DepotPrivileges::THESE_CONSULTATION_DEPOT,
                    'assertion'  => TheseAssertion::class,
                ],
                [
                    'controller' => ValidationController::class,
                    'action'     => [
                        'modifier-validation-depot-these-corrigee',
                    ],
                    'privileges' => ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE,
                    'assertion'  => TheseAssertion::class,
                ],
                [
                    'controller' => ValidationController::class,
                    'action'     => [
                        'modifier-validation-correction-these',
                    ],
                    'privileges' => ValidationPrivileges::VALIDATION_CORRECTION_THESE,
                    'assertion'  => TheseAssertion::class,
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'validation' => [
                'type'          => 'Literal',
                'options'       => [
                    'route'    => '/validation',
                    'defaults' => [
                        'controller'=> ValidationController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'page-de-couverture' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/page-de-couverture/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'page-de-couverture',
                            ],
                        ],
                    ],
                    'rdv-bu' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/rdv-bu/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'rdv-bu',
                            ],
                        ],
                    ],
                    'validation-depot-these-corrigee' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/validation-depot-these-corrigee/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'validation-depot-these-corrigee',
                            ],
                        ],
                    ],
                    'modifier-validation-depot-these-corrigee' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/modifier-validation-depot-these-corrigee/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'modifier-validation-depot-these-corrigee',
                            ],
                        ],
                    ],
                    'validation-correction-these' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/validation-correction-these/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'validation-correction-these',
                            ],
                        ],
                    ],
                    'modifier-validation-correction-these' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route'       => '/modifier-validation-correction-these/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'modifier-validation-correction-these',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            DepotValidationService::class => DepotValidationServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            ValidationController::class => ValidationControllerFactory::class,
        ],
    ],
];
