<?php

use Application\Controller\Factory\ValidationControllerFactory;
use Application\Provider\Privilege\ThesePrivileges;
use Application\Provider\Privilege\ValidationPrivileges;
use Application\Service\Validation\ValidationService;
use Application\View\Helper\ValidationViewHelper;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'These' => [],
            ],
        ],
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
                        'assertion'  => 'Assertion\\These',
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Validation',
                    'action'     => [
                        'page-de-couverture',
                    ],
                    'privileges' => ValidationPrivileges::VALIDATION_PAGE_DE_COUVERTURE,
//                    'assertion'  => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\Validation',
                    'action'     => [
                        'rdv-bu',
                    ],
                    'privileges' => ValidationPrivileges::THESE_VALIDATION_RDV_BU,
                    'assertion'  => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\Validation',
                    'action'     => [
                        'validation-depot-these-corrigee',
                        'validation-correction-these',
                    ],
                    'privileges' => ThesePrivileges::THESE_CONSULTATION_DEPOT,
                    'assertion'  => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\Validation',
                    'action'     => [
                        'modifier-validation-depot-these-corrigee',
                    ],
                    'privileges' => ValidationPrivileges::VALIDATION_DEPOT_THESE_CORRIGEE,
                    'assertion'  => 'Assertion\\These',
                ],
                [
                    'controller' => 'Application\Controller\Validation',
                    'action'     => [
                        'modifier-validation-correction-these',
                    ],
                    'privileges' => ValidationPrivileges::VALIDATION_CORRECTION_THESE,
                    'assertion'  => 'Assertion\\These',
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
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Validation',
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
        'invokables' => [
            'ValidationService' => ValidationService::class,
        ],
        'factories' => [
        ],
    ],
    'controllers' => [
        'factories' => [
            'Application\Controller\Validation' => ValidationControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'validation'  => ValidationViewHelper::class,
        ],
    ],
];
