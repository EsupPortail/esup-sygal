<?php

namespace Admission;

use Admission\Assertion\ConventionFormationDoctorale\ConventionFormationDoctoraleAssertion;
use Admission\Assertion\ConventionFormationDoctorale\ConventionFormationDoctoraleAssertionFactory;
use Admission\Controller\ConventionFormationDoctorale\ConventionFormationDoctoraleController;
use Admission\Controller\ConventionFormationDoctorale\ConventionFormationDoctoraleControllerFactory;
use Admission\Form\ConventionFormationDoctorale\ConventionFormationDoctoraleForm;
use Admission\Form\ConventionFormationDoctorale\ConventionFormationDoctoraleFormFactory;
use Admission\Hydrator\ConventionFormationDoctorale\ConventionFormationDoctoraleHydrator;
use Admission\Hydrator\ConventionFormationDoctorale\ConventionFormationDoctoraleHydratorFactory;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Service\ConventionFormationDoctorale\ConventionFormationDoctoraleService;
use Admission\Service\ConventionFormationDoctorale\ConventionFormationDoctoraleServiceFactory;
use Admission\Service\Exporter\ConventionFormationDoctorale\ConventionFormationDoctoraleExporter;
use Admission\Service\Exporter\ConventionFormationDoctorale\ConventionFormationDoctoraleExporterFactory;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return array(
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'ConventionFormationDoctorale' => []
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            AdmissionPrivileges::ADMISSION_CONVENTION_FORMATION_MODIFIER,
                            AdmissionPrivileges::ADMISSION_CONVENTION_FORMATION_VISUALISER,
                            AdmissionPrivileges::ADMISSION_CONVENTION_FORMATION_GENERER,
                        ],
                        'resources'  => ['ConventionFormationDoctorale'],
                        'assertion'  => ConventionFormationDoctoraleAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ConventionFormationDoctoraleController::class,
                    'action' => [
                        'modifier-convention-formation',
                        'ajouter-convention-formation',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_CONVENTION_FORMATION_MODIFIER,
                        AdmissionPrivileges::ADMISSION_CONVENTION_FORMATION_VISUALISER,
                    ],
                    'assertion' => ConventionFormationDoctoraleAssertion::class,
                ],
                [
                    'controller' => ConventionFormationDoctoraleController::class,
                    'action' => [
                        'generer-convention-formation',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_CONVENTION_FORMATION_GENERER,
                    ],
                    'assertion' => ConventionFormationDoctoraleAssertion::class,
                ],
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'admission' => [
                'child_routes' => [
                    'generer-convention-formation' => [
                        'type'  => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/generer-convention-formation/:admission/convention-formation-doctorale',
                            'defaults' => [
                                'controller' => ConventionFormationDoctoraleController::class,
                                'action'     => 'generer-convention-formation',
                                /* @see ConventionFormationDoctoraleController::genererConventionFormationAction() */
                            ],
                        ],
                    ],
                    'ajouter-convention-formation' => [
                        'type'  => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/convention-formation/ajouter/:admission',
                            'defaults' => [
                                'controller' => ConventionFormationDoctoraleController::class,
                                'action'     => 'ajouter-convention-formation',
                                /* @see ConventionFormationDoctoraleController::ajouterConventionFormationAction() */
                            ],
                        ],
                    ],
                    'modifier-convention-formation' => [
                        'type'  => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/convention-formation/modifier/:admission',
                            'defaults' => [
                                'controller' => ConventionFormationDoctoraleController::class,
                                'action'     => 'modifier-convention-formation',
                                /* @see ConventionFormationDoctoraleController::modifierConventionFormationAction() */
                            ],
                        ],
                    ],
                ]
            ]
        ]
    ],

    'controllers' => [
        'factories' => [
            ConventionFormationDoctoraleController::class => ConventionFormationDoctoraleControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            ConventionFormationDoctoraleForm::class => ConventionFormationDoctoraleFormFactory::class
        ],
    ],

    'service_manager' => [
        'factories' => [
            ConventionFormationDoctoraleService::class => ConventionFormationDoctoraleServiceFactory::class,
            ConventionFormationDoctoraleAssertion::class => ConventionFormationDoctoraleAssertionFactory::class,
            ConventionFormationDoctoraleExporter::class => ConventionFormationDoctoraleExporterFactory::class
        ],
    ],

    'hydrators' => [
        'factories' => [
            ConventionFormationDoctoraleHydrator::class => ConventionFormationDoctoraleHydratorFactory::class
        ],
    ],
);
