<?php

use Application\Controller\Factory\TableauDeBordControllerFactory;
use Application\Controller\TableauDeBordController;
use Application\Provider\Privilege\EcoleDoctoralePrivileges;
use Application\Service\AnomalieService;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => TableauDeBordController::class,
                    'action'     => [
                        'index',
                        'anomalie',
                    ],
                    'privileges' => EcoleDoctoralePrivileges::ECOLE_DOCT_CONSULTATION,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'tableau-de-bord' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/tableau-de-bord',
                    'defaults' => [
                        'controller'    => TableauDeBordController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'anomalie' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/anomalie',
                            'defaults'    => [
                                'action' => 'anomalie',
                            ],
                        ],
                    ],
                ],
            ],

        ],
    ],
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [
            AnomalieService::class => AnomalieService::class,
        ],
        'factories' => [],
    ],
    'controllers'     => [
        'invokables' => [
        ],
        'factories' => [
            TableauDeBordController::class => TableauDeBordControllerFactory::class,
        ],
    ],
    'form_elements'   => [
        'invokables' => [],
        'factories' => [],
    ],
    'hydrators' => [
        'invokables' => [],
        'factories' => [],
    ],
    'view_helpers' => [
        'invokables' => [],
    ],
];
