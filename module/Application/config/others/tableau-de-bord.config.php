<?php

use Application\Controller\Factory\TableauDeBordControllerFactory;
use Application\Controller\TableauDeBordController;
use Structure\Provider\Privilege\StructurePrivileges;
use Application\Service\AnomalieService;
use UnicaenPrivilege\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

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
                    'privileges' => [
                        StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES,
                    ],
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
