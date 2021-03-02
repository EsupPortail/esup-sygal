<?php

use ComiteSuivi\Controller\ComiteSuiviController;
use ComiteSuivi\Controller\IndexController;
use ComiteSuivi\Controller\IndexControllerFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndexController::class,
                    'action'     => [
                        'index',
                    ],
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'comite-suivi' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/comite-suivi',
                    'defaults' => [
                        'controller'    => IndexController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
            ],
        ],
    ],

    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'comite-suivi' => [
                        'label'    => 'Comité de suivi',
                        'route'    => 'comite-suivi',
                        'resource' => PrivilegeController::getResourceId(ComiteSuiviController::class, 'index'),
                        'order'    => -100,
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
        ],
    ],
    'controllers'     => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
        ],
    ],
    'form_elements'   => [
        'factories' => [
        ],
    ],
    'hydrators' => [
        'factories' => [
        ],
    ],
    'view_helpers' => [
        'invokables' => [
        ],
        'factories' => [],
    ],
];
