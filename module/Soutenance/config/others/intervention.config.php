<?php

namespace Soutenance;

use Application\Navigation\ApplicationNavigationFactory;
use HDR\Entity\Db\HDR;
use Soutenance\Assertion\InterventionAssertion;
use Soutenance\Assertion\InterventionAssertionFactory;
use Soutenance\Controller\InterventionController;
use Soutenance\Controller\InterventionControllerFactory;
use Soutenance\Provider\Privilege\InterventionPrivileges;
use Soutenance\Service\Intervention\InterventionService;
use Soutenance\Service\Intervention\InterventionServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;
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
                            InterventionPrivileges::INTERVENTION_AFFICHER,
                            InterventionPrivileges::INTERVENTION_MODIFIER,
                        ],
                        'resources' => ['These', 'HDR'],
                        'assertion' => InterventionAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => InterventionController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => InterventionPrivileges::INTERVENTION_INDEX,
                ],
                [
                    'controller' => InterventionController::class,
                    'action' => [
                        'afficher',
                    ],
                    'privileges' => InterventionPrivileges::INTERVENTION_AFFICHER,
                    'assertion' => InterventionAssertion::class,
                ],
                [
                    'controller' => InterventionController::class,
                    'action' => [
                        'toggle-president-distanciel',
                        'ajouter-visioconference-tardive',
                        'supprimer-visioconference-tardive',
                    ],
                    'privileges' => InterventionPrivileges::INTERVENTION_MODIFIER,
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    /**
                     * Navigation pour LA thèse courante.
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::THESE_SELECTIONNEE_PAGE_ID => [
                        'pages' => [
                            // DEPTH = 2
                            'soutenance_these' => [
                                'pages' => [
                                    // DEPTH = 3
                                    'intervention' => [
                                        'label' => 'Intervention de soutenance',
                                        'route' => 'soutenance_these/intervention/afficher',
                                        'order' => 250,
                                        'resource' => InterventionPrivileges::getResourceId(InterventionPrivileges::INTERVENTION_AFFICHER),
                                        'withtarget' => true,
//                                        'paramsInject' => [
//                                            'these',
//                                        ],
                                        'paramsInject' => [
                                            'type',
                                            'these',
                                            'id',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],

                    /**
                     * Navigation pour LA HDR courante.
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::HDR_SELECTIONNEE_PAGE_ID => [
                        'pages' => [
                            // DEPTH = 2
                            'soutenance_hdr' => [
                                'pages' => [
                                    // DEPTH = 3
                                    'intervention' => [
                                        'label' => 'Intervention de soutenance',
                                        'route' => 'soutenance_hdr/intervention/afficher',
                                        'order' => 250,
                                        'resource' => InterventionPrivileges::getResourceId(InterventionPrivileges::INTERVENTION_AFFICHER),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'hdr',
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

    'router' => [
        'routes' => [
            'soutenance_these' => [
                'child_routes' => $soutenanceChildRoutes = [
                    'intervention' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/intervention',
                            'defaults' => [
                                'controller' => InterventionController::class,
                                'action' => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'afficher' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/afficher',
                                    'defaults' => [
                                        'action' => 'afficher',
                                    ],
                                ],
                            ],
                            'toggle-president-distanciel' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/toggle-president-distanciel',
                                    'defaults' => [
                                        'action' => 'toggle-president-distanciel',
                                    ],
                                ],
                            ],
                            'ajouter-visioconference-tardive' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/ajouter-visioconference-tardive',
                                    'defaults' => [
                                        'action' => 'ajouter-visioconference-tardive',
                                    ],
                                ],
                            ],
                            'supprimer-visioconference-tardive' => [
                                'type' => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/supprimer-visioconference-tardive/:intervention',
                                    'defaults' => [
                                        'action' => 'supprimer-visioconference-tardive',
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
            InterventionAssertion::class => InterventionAssertionFactory::class,
            InterventionService::class => InterventionServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            InterventionController::class => InterventionControllerFactory::class,
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