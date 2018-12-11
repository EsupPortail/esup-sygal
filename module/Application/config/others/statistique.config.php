<?php

use Application\Provider\Privilege\StructurePrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Application\Controller\Factory\StatistiqueControllerFactory;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Statistique',
                    'action'     => [
                        'index',
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
                                'resource' => PrivilegeController::getResourceId('Application\Controller\EcoleDoctorale', 'index'),
                                'order'    => 90,
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
