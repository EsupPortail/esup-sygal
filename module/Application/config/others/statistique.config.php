<?php

use Application\Controller\Factory\StatistiqueControllerFactory;
use Indicateur\Provider\Privilege\IndicateurPrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Statistique',
                    'action'     => [
                        'index',
                    ],
                    'privileges' => IndicateurPrivileges::INDICATEUR_STATISTIQUE,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'statistique' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/statistique',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Statistique',
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
                    'admin' => [
                        'pages' => [
                            'statistique' => [
                                'label'    => 'Statistiques',
                                'route'    => 'statistique',
                                'resource' => IndicateurPrivileges::getResourceId(IndicateurPrivileges::INDICATEUR_STATISTIQUE),
                                'order'    => 500,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [],
        'factories' => [],
    ],
    'controllers'     => [
        'invokables' => [],
        'factories' => [
            'Application\Controller\Statistique' => StatistiqueControllerFactory::class,
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
