<?php

namespace Formation;

use Formation\Controller\IndexController;
use Formation\Controller\IndexControllerFactory;
use Formation\Provider\Privilege\IndexPrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => [
                        IndexPrivileges::INDEX_AFFICHER,
                    ],
                ],
            ],
        ],
    ],

    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'formation' => [
                        'label'    => 'Formations',
                        'route'    => 'formation',
                        'resource' => PrivilegeController::getResourceId(IndexController::class, 'index') ,
                        'order'    => 9999,
                    ],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'formation' => [
                'type'  => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/formation',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [],
    ],
    'controllers'     => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [],
    ],
    'hydrators' => [
        'factories' => [],
    ]

];