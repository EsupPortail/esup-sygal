<?php

use Indicateur\Controller\Factory\IndicateurControllerFactory;
use Indicateur\Controller\IndicateurController;
use Zend\Mvc\Router\Http\Segment;

return array(
    'bjyauthorize'    => [
        'guards' => [
            \UnicaenAuth\Guard\PrivilegeController::class => [
                [
                    'controller' => IndicateurController::class,
                    'action'     => [
                        'index',
                        'soutenance-depassee',
                        'export-soutenance-depassee',
                        'acteurs-sans-mail',
                        'export-acteurs-sans-mail',
                ],
                    'roles' => [
                        'Administrateur technique',
                    ],
                ],
            ],
        ],
    ],
    'doctrine'     => [],

    'router' => [
        'routes' => [
            'indicateur' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/indicateur',
                    'defaults' => [
                        'controller' => IndicateurController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes'  => [
                    'soutenance-depassee' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/soutenance-depassee',
                            'defaults'    => [
                                'action' => 'soutenance-depassee',
                            ],
                        ],
                        'child_routes'  => [
                            'export' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/export',
                                    'defaults'    => [
                                        'action' => 'export-soutenance-depassee',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'acteurs-sans-mail' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/acteurs-sans-mail',
                            'defaults'    => [
                                'action' => 'acteurs-sans-mail',
                            ],
                        ],
                        'child_routes'  => [
                            'export' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/export',
                                    'defaults'    => [
                                        'action' => 'export-acteurs-sans-mail',
                                    ],
                                ],
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
            IndicateurController::class => IndicateurControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
);
