<?php

namespace Admission;

use Admission\Assertion\Avis\AdmissionAvisAssertion;
use Admission\Assertion\Avis\AdmissionAvisAssertionFactory;
use Admission\Config\ModuleConfig;
use Admission\Config\ModuleConfigFactory;
use Admission\Controller\Avis\AdmissionAvisController;
use Admission\Controller\Avis\AdmissionAvisControllerFactory;
use Admission\Controller\Validation\AdmissionValidationController;
use Admission\Event\Avis\AdmissionAvisEventListener;
use Admission\Event\Avis\AdmissionAvisEventListenerFactory;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Rule\Operation\AdmissionOperationRule;
use Admission\Rule\Operation\AdmissionOperationRuleFactory;
use Admission\Rule\Operation\Notification\OperationAttendueNotificationRule;
use Admission\Rule\Operation\Notification\OperationAttendueNotificationRuleFactory;
use Admission\Service\Avis\AdmissionAvisService;
use Admission\Service\Avis\AdmissionAvisServiceFactory;
use Admission\Service\Notification\NotificationFactory;
use Admission\Service\Notification\NotificationFactoryFactory;
use Admission\Service\Operation\AdmissionOperationService;
use Admission\Service\Operation\AdmissionOperationServiceFactory;
use Laminas\Router\Http\Segment;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return array(
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'AdmissionAvis' => []
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            AdmissionPrivileges::ADMISSION_AJOUTER_AVIS_SIEN,
                            AdmissionPrivileges::ADMISSION_AJOUTER_AVIS_TOUT,
                            AdmissionPrivileges::ADMISSION_MODIFIER_AVIS_SIEN,
                            AdmissionPrivileges::ADMISSION_MODIFIER_AVIS_TOUT,
                            AdmissionPrivileges::ADMISSION_SUPPRIMER_AVIS_SIEN,
                            AdmissionPrivileges::ADMISSION_SUPPRIMER_AVIS_TOUT
                        ],
                        'resources'  => ['AdmissionAvis'],
                        'assertion'  => AdmissionAvisAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => AdmissionAvisController::class,
                    'action' => [
                        'aviser',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_AJOUTER_AVIS_SIEN,
                        AdmissionPrivileges::ADMISSION_AJOUTER_AVIS_TOUT,
                    ],
                    'assertion' => AdmissionAvisAssertion::class,
                ],
                [
                    'controller' => AdmissionAvisController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_MODIFIER_AVIS_SIEN,
                        AdmissionPrivileges::ADMISSION_MODIFIER_AVIS_TOUT,
                    ],
                    'assertion' => AdmissionAvisAssertion::class,
                ],
                [
                    'controller' => AdmissionAvisController::class,
                    'action' => [
                        'desaviser',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_SUPPRIMER_AVIS_SIEN,
                        AdmissionPrivileges::ADMISSION_SUPPRIMER_AVIS_TOUT,
                    ],
                    'assertion' => AdmissionAvisAssertion::class,
                ]
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'admission' => [
                'child_routes' => [
                    'aviser' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/avis/ajouter/:admission/type/:typeAvis',
                            'constraints' => [
                                'admission' => '\d+',
                                'typeAvis' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'aviser',
                                /* @see AdmissionAvisController::aviserAction */
                                'controller' => AdmissionAvisController::class,
                            ],
                        ],
                    ],
                    'modifierAvis' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/avis/modifier/:admissionAvis',
                            'constraints' => [
                                'admissionAvis' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'modifier',
                                'controller' => AdmissionAvisController::class,
                                /* @see AdmissionAvisController::modifierAction */
                            ],
                        ],
                    ],
                    'desaviser' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/avis/supprimer/:admissionAvis',
                            'constraints' => [
                                'admissionAvis' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'desaviser',
                                'controller' => AdmissionAvisController::class,
                                /* @see AdmissionAvisController::desaviserAction */
                            ],
                        ],
                    ],
                ],
            ]
        ]
    ],

    'controllers' => [
        'factories' => [
            AdmissionAvisController::class => AdmissionAvisControllerFactory::class,
        ],
    ],

    'service_manager' => [
        'factories' => [
            AdmissionAvisService::class => AdmissionAvisServiceFactory::class,

            AdmissionAvisEventListener::class => AdmissionAvisEventListenerFactory::class,

            AdmissionAvisAssertion::class => AdmissionAvisAssertionFactory::class,
        ],
    ],
);
