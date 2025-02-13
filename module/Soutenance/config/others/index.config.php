<?php

namespace Soutenance;

use Application\Navigation\ApplicationNavigationFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Soutenance\Assertion\IndexAssertion;
use Soutenance\Assertion\IndexAssertionFactory;
use Soutenance\Controller\HDR\Proposition\PropositionHDRRechercheController;
use Soutenance\Controller\IndexController;
use Soutenance\Controller\IndexControllerFactory;
use Soutenance\Controller\These\PropositionThese\PropositionTheseRechercheController;
use Soutenance\Provider\Privilege\IndexPrivileges;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return array(
    'bjyauthorize' => [
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            IndexPrivileges::INDEX_RAPPORTEUR,
                            IndexPrivileges::INDEX_GLOBAL,
                        ],
                        'resources' => ['These', 'HDR'],
                        'assertion' => IndexAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => IndexPrivileges::INDEX_GLOBAL,
                ],
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index-acteur',
                    ],
                    'privileges' => IndexPrivileges::INDEX_ACTEUR,
                    'assertion' => IndexAssertion::class,
                ],
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index-rapporteur',
                    ],
                    'privileges' => IndexPrivileges::INDEX_RAPPORTEUR,
                    'assertion' => IndexAssertion::class,
                ],
                [
                    'controller' => PropositionHDRRechercheController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => IndexPrivileges::INDEX_STRUCTURE,
                ],
                [
                    'controller' => PropositionTheseRechercheController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => IndexPrivileges::INDEX_STRUCTURE,
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            // DEPTH = 0
            'home' => [
                'pages' => [
                    /**
                     * Cette page aura une page fille 'these-1', 'these-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::NOS_THESES_PAGE_ID => [
                        'pages' => [
                            // DEPTH = 2
                            'SOUTENANCES' => [
                                'label' => '(Soutenances Structure)',
                                'route' => 'soutenances/index-structure-these',
//                                'resource' => PrivilegeController::getResourceId(TheseController::class, 'index'),
                            ],
                        ],
                    ],
                    /**
                     * Cette page aura une page fille 'hdr-1', 'hdr-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::NOS_HDR_PAGE_ID => [
                        'pages' => [
                            // DEPTH = 2
                            'SOUTENANCES' => [
                                'label' => '(Soutenances Structure)',
                                'route' => 'soutenances/index-structure-hdr',
//                                'resource' => PrivilegeController::getResourceId(TheseController::class, 'index'),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'soutenances' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/soutenances',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'child_routes' => [
                    'recherche-these' => [
                        'type' => Literal::class,
                        'may_terminate' => false,
                        'options' => [
                            'route' => '/recherche',
                            'defaults' => [
                                'controller' => PropositionTheseRechercheController::class,
                                'type' => 'these', // requis
                            ],
                        ],
                        'child_routes' => [
                            'filters' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'recherche-hdr' => [
                        'type' => Literal::class,
                        'may_terminate' => false,
                        'options' => [
                            'route' => '/recherche',
                            'defaults' => [
                                'controller' => PropositionHDRRechercheController::class,
                                'type' => 'hdr', // requis
                            ],
                        ],
                        'child_routes' => [
                            'filters' => [
                                'type' => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'index-structure-hdr' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/hdr/index-structure',
                            'defaults' => [
//                                'controller' => IndexController::class,
//                                'action' => 'index-structure',
                                'controller' => PropositionHDRRechercheController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'index-structure-these' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/these/index-structure',
                            'defaults' => [
//                                'controller' => IndexController::class,
//                                'action' => 'index-structure',
                                'controller' => PropositionTheseRechercheController::class,
                                'action' => 'index',
                            ],
                        ],
                    ],
                    'index-rapporteur' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/index-rapporteur',
                            'defaults' => [
                                'controller' => IndexController::class,
                                'action' => 'index-rapporteur',
                            ],
                        ],
                    ],
                    'index-acteur' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/index-acteur',
                            'defaults' => [
                                'controller' => IndexController::class,
                                'action' => 'index-acteur',
                            ],
                        ],
                    ],
                ],
            ],
            'soutenance_these' => [
                'type' => Segment::class,
                'may_terminate' => false,
                'options' => [
                    'route' => '/soutenance/these/:these',
                    'constraints' => [
                        'these' => '\d+',
                    ],
                    'defaults' => [
                        'controller' => IndexController::class,
                        'type' => 'these', // requis
                    ],
                ],
                'child_routes' => $soutenanceChildRoutes = [
                    'index-rapporteur' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/index-rapporteur',
                            'defaults' => [
                                'controller' => IndexController::class,
                                'action' => 'index-rapporteur',
                            ],
                        ],
                    ],
                ],
            ],
            'soutenance_hdr' => [
                'type' => Segment::class,
                'may_terminate' => false,
                'options' => [
                    'route' => '/soutenance/hdr/:hdr',
                    'constraints' => [
                        'hdr' => '\d+',
                    ],
                    'defaults' => [
                        'controller' => IndexController::class,
                        'type' => 'hdr', // requis
                    ],
                ],
                'child_routes' => $soutenanceChildRoutes,
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            IndexAssertion::class => IndexAssertionFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
        ],
    ],
);
