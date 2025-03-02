<?php

namespace Soutenance;

use Application\Navigation\ApplicationNavigationFactory;
use Soutenance\Controller\IndexController;
use Soutenance\Controller\IndexControllerFactory;
use Soutenance\Controller\PropositionRechercheController;
use Soutenance\Provider\Privilege\IndexPrivileges;
use UnicaenPrivilege\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return array(
    'bjyauthorize' => [
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
                ],
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index-rapporteur',
                    ],
                    'privileges' => IndexPrivileges::INDEX_RAPPORTEUR,
                ],
                [
                    'controller' => PropositionRechercheController::class,
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
                                'route' => 'soutenances/index-structure',
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
                    'recherche' => [
                        'type' => Literal::class,
                        'may_terminate' => false,
                        'options' => [
                            'route' => '/recherche',
                            'defaults' => [
                                'controller' => PropositionRechercheController::class,
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
                    'index-structure' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/index-structure',
                            'defaults' => [
//                                'controller' => IndexController::class,
//                                'action' => 'index-structure',
                                'controller' => PropositionRechercheController::class,
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
            'soutenance' => [
                'type' => Segment::class,
                'may_terminate' => false,
                'options' => [
                    'route' => '/soutenance/:these',
                    'constraints' => [
                        'these' => '\d+',
                    ],
                    'defaults' => [
                        'controller' => IndexController::class,
                    ],
                ],
                'child_routes' => [
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
        ],
    ],

    'service_manager' => [
        'factories' => [],
    ],
    'controllers' => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
        ],
    ],
);
