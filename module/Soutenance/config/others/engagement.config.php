<?php

namespace Soutenance;

use Soutenance\Assertion\EngagementImpartialiteAssertion;
use Soutenance\Assertion\EngagementImpartialiteAssertionFactory;
use Soutenance\Controller\EngagementImpartialiteController;
use Soutenance\Controller\EngagementImpartialiteControllerFactory;
use Soutenance\Provider\Privilege\EngagementImpartialitePrivileges;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteService;
use Soutenance\Service\EngagementImpartialite\EngagementImpartialiteServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;
use Laminas\Router\Http\Segment;

return array(
    'bjyauthorize' => [
//        'resource_providers' => [
//            'BjyAuthorize\Provider\Resource\Config' => [
//                'Acteur' => [],
//            ],
//        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_SIGNER,
                            EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_ANNULER,
                            EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_NOTIFIER,
                            EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_VISUALISER,
                        ],
                        'resources' => ['These', 'HDR'],
                        'assertion' => EngagementImpartialiteAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => EngagementImpartialiteController::class,
                    'action' => [
                        'engagement-impartialite',
                    ],
                    'privileges' => EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_VISUALISER,
                ],
                [
                    'controller' => EngagementImpartialiteController::class,
                    'action' => [
                        'notifier-engagement-impartialite',
                    ],
                    'privileges' => EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_NOTIFIER,
                    'assertion' => EngagementImpartialiteAssertion::class,
                ],
                [
                    'controller' => EngagementImpartialiteController::class,
                    'action' => [
                        'signer-engagement-impartialite',
                        'refuser-engagement-impartialite',
                    ],
                    'privileges' => EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_SIGNER,
                ],
                [
                    'controller' => EngagementImpartialiteController::class,
                    'action' => [
                        'annuler-engagement-impartialite',
                    ],
                    'privileges' => EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_ANNULER,
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'soutenance_these' => [
                'child_routes' => $soutenanceChildRoutes = [
                    'engagement-impartialite' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/engagement-impartialite/:membre',
                            'defaults' => [
                                'controller' => EngagementImpartialiteController::class,
                                'action' => 'engagement-impartialite',
                            ],
                        ],
                        'child_routes' => [
                            'notifier' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/notifier',
                                    'defaults' => [
                                        'controller' => EngagementImpartialiteController::class,
                                        'action' => 'notifier-engagement-impartialite',
                                    ],
                                ],
                            ],
                            'signer' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/signer',
                                    'defaults' => [
                                        'controller' => EngagementImpartialiteController::class,
                                        'action' => 'signer-engagement-impartialite',
                                    ],
                                ],
                            ],
                            'refuser' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/refuser',
                                    'defaults' => [
                                        'controller' => EngagementImpartialiteController::class,
                                        'action' => 'refuser-engagement-impartialite',
                                    ],
                                ],
                            ],
                            'annuler' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/annuler',
                                    'defaults' => [
                                        'controller' => EngagementImpartialiteController::class,
                                        'action' => 'annuler-engagement-impartialite',
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
            EngagementImpartialiteService::class => EngagementImpartialiteServiceFactory::class,
            EngagementImpartialiteAssertion::class => EngagementImpartialiteAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
        ],
        'factories' => [
            EngagementImpartialiteController::class => EngagementImpartialiteControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
        ],
    ],

    'hydrators' => [
        'invokables' => [
        ],
    ],
);
