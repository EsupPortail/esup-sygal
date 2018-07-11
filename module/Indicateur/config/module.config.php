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
                        'doctorants-sans-mail',
                        'export-doctorants-sans-mail',
                        'theses-anciennes',
                        'export-theses-anciennes',
                        'theses-a-soutenir',
                        'export-theses-a-soutenir',
                ],
                    'roles' => [
                        'Administrateur technique',
                    ],
                ],
            ],
        ],
    ],
    'doctrine'     => [],

    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'indicateur' => [
                                'label'    => 'Indicateurs',
                                'route'    => 'indicateur',
                                //'resource' => PrivilegeController::getResourceId('Application\Controller\EcoleDoctorale', 'index'),
                                'roles' => [
                                    'Administrateur technique',
                                ],

                                'order'    => 1,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

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
                    'theses-a-soutenir' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/theses-a-soutenir',
                            'defaults'    => [
                                'action' => 'theses-a-soutenir',
                            ],
                        ],
                        'child_routes'  => [
                            'export' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/export',
                                    'defaults'    => [
                                        'action' => 'export-theses-a-soutenir',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'doctorants-sans-mail' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/doctorants-sans-mail',
                            'defaults'    => [
                                'action' => 'doctorants-sans-mail',
                            ],
                        ],
                        'child_routes'  => [
                            'export' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/export',
                                    'defaults'    => [
                                        'action' => 'export-doctorants-sans-mail',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'theses-anciennes' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/theses-anciennes',
                            'defaults'    => [
                                'action' => 'theses-anciennes',
                            ],
                        ],
                        'child_routes'  => [
                            'export' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/export',
                                    'defaults'    => [
                                        'action' => 'export-theses-anciennes',
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
