<?php

use Application\Service\ServiceAwareInitializer;
use Retraitement\Controller\IndexControllerFactory;
use Retraitement\Service\RetraitementServiceFactory;

return [
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
//                'These' => [],
            ],
        ],
        'rule_providers'     => [

        ],
        'guards' => [
            \UnicaenAuth\Guard\PrivilegeController::class => [
                [
                    'controller' => 'Retraitement\Controller\Index',
                    'action'     => [
                        'index',
                    ],
                    'privileges' => \Application\Provider\Privilege\ThesePrivileges::THESE_RECHERCHE,
                ],
                [
                    'controller' => 'Retraitement\Controller\Index',
                    'action'     => [
                        'retraiter-console',
                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'application' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/retraitement',
                    'defaults' => [
                        '__NAMESPACE__' => 'Retraitement\Controller',
                        'controller'    => 'Index',
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'default' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/[:controller[/:action]]',
                            'constraints' => [
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ],
                            'defaults' => [
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
//                    'droits' => [
//                        'order' => -80,
//                    ],
                ],
            ],
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'retraiter-console' => [
                    'options' => [
                        'route'    => 'fichier retraiter [--tester-archivabilite] [--notifier=] <fichier>',
                        'defaults' => [
                            'controller' => 'Retraitement\Controller\Index',
                            'action'     => 'retraiter-console',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [

        ],
        'factories' => [
            'RetraitementService' => RetraitementServiceFactory::class,
        ],
        'initializers' => [
            ServiceAwareInitializer::class,
        ]
    ],

    'controllers' => [
        'invokables' => [
        ],
        'factories' => [
            'Retraitement\Controller\Index' => IndexControllerFactory::class
        ],
        'initializers' => [
            ServiceAwareInitializer::class,
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];