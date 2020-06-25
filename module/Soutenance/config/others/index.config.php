<?php

namespace Soutenance;

use Soutenance\Controller\IndexController;
use Soutenance\Controller\IndexControllerFactory;
use Soutenance\Provider\Privilege\IndexPrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

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

    'router' => [
        'routes' => [
            'soutenance' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/soutenance',
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
                            'route' => '/index-rapporteur[/:these]',
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
