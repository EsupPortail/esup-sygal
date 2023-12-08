<?php

namespace Admission;

use Admission\Assertion\AdmissionAssertion;
use Admission\Assertion\Validation\AdmissionValidationAssertion;
use Admission\Assertion\Validation\AdmissionValidationAssertionFactory;
use Admission\Config\ModuleConfig;
use Admission\Config\ModuleConfigFactory;
use Admission\Controller\AdmissionController;
use Admission\Controller\Validation\AdmissionValidationController;
use Admission\Controller\Validation\AdmissionValidationControllerFactory;
use Admission\Event\Validation\AdmissionValidationEventListener;
use Admission\Event\Validation\AdmissionValidationEventListenerFactory;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Rule\Operation\AdmissionOperationRule;
use Admission\Rule\Operation\AdmissionOperationRuleFactory;
use Admission\Rule\Operation\Notification\OperationAttendueNotificationRule;
use Admission\Rule\Operation\Notification\OperationAttendueNotificationRuleFactory;
use Admission\Service\Notification\NotificationFactory;
use Admission\Service\Notification\NotificationFactoryFactory;
use Admission\Service\Operation\AdmissionOperationService;
use Admission\Service\Operation\AdmissionOperationServiceFactory;
use Admission\Service\Validation\AdmissionValidationService;
use Admission\Service\Validation\AdmissionValidationServiceFactory;
use Laminas\Router\Http\Segment;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return array(
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'AdmissionValidation' => []
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            AdmissionPrivileges::ADMISSION_VALIDER_SIEN,
                            AdmissionPrivileges::ADMISSION_VALIDER_TOUT,
                            AdmissionPrivileges::ADMISSION_DEVALIDER_SIEN,
                            AdmissionPrivileges::ADMISSION_DEVALIDER_TOUT
                        ],
                        'resources'  => ['AdmissionValidation'],
                        'assertion'  => AdmissionValidationAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => AdmissionValidationController::class,
                    'action' => [
                        'valider',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_VALIDER_TOUT,
                        AdmissionPrivileges::ADMISSION_VALIDER_SIEN,
                    ],
                    'assertion' => AdmissionValidationAssertion::class,
                ],
                [
                    'controller' => AdmissionValidationController::class,
                    'action' => [
                        'devalider',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_DEVALIDER_TOUT,
                        AdmissionPrivileges::ADMISSION_DEVALIDER_SIEN,
                    ],
                    'assertion' => AdmissionValidationAssertion::class,
                ],
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'admission' => [
                'child_routes' => [
                    'valider' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/valider/:admission/type/:typeValidation',
                            'constraints' => [
                                'admission' => '\d+',
                                'typeValidation' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => AdmissionValidationController::class,
                                'action' => 'valider',
                                /* @see AdmissionValidationController::validerAction() */
                            ],
                        ],
                    ],
                    'devalider' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/devalider/:admissionValidation',
                            'constraints' => [
                                'admissionValidation' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => AdmissionValidationController::class,
                                'action' => 'devalider',
                                /* @see AdmissionValidationController::devaliderAction() */
                            ],
                        ],
                    ],
                ]
            ]
        ]
    ],

    'controllers' => [
        'factories' => [
            AdmissionValidationController::class => AdmissionValidationControllerFactory::class,
        ],
    ],

    'service_manager' => [
        'factories' => [
            ModuleConfig::class => ModuleConfigFactory::class,
            NotificationFactory::class => NotificationFactoryFactory::class,

            AdmissionValidationService::class => AdmissionValidationServiceFactory::class,
            AdmissionOperationService::class => AdmissionOperationServiceFactory::class,

            AdmissionValidationEventListener::class => AdmissionValidationEventListenerFactory::class,

            OperationAttendueNotificationRule::class => OperationAttendueNotificationRuleFactory::class,
            AdmissionOperationRule::class => AdmissionOperationRuleFactory::class,

            AdmissionValidationAssertion::class => AdmissionValidationAssertionFactory::class,
        ],
    ],
);
