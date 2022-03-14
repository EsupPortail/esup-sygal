<?php

namespace Soutenance;

use Application\Navigation\ApplicationNavigationFactory;
use Soutenance\Controller\IndexController;
use Soutenance\Controller\IndexControllerFactory;
use Soutenance\Provider\Privilege\IndexPrivileges;
use UnicaenAuth\Guard\PrivilegeController;
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
                    'roles' => [],
                    'privileges' => IndexPrivileges::INDEX_RAPPORTEUR,
                ],
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index-structure',
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
//                                'resource' => PrivilegeController::getResourceId('Application\Controller\These', 'index'),
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
                    'index-structure' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/index-structure',
                            'defaults' => [
                                'controller' => IndexController::class,
                                'action' => 'index-structure',
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
