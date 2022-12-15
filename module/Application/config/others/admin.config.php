<?php

use Application\Controller\AdminController;
use Application\Controller\Factory\PrivilegeControllerFactory;
use Application\Controller\Factory\RoleControllerFactory;
use Application\Controller\RoleController;
use Application\Provider\Privilege\UtilisateurPrivileges;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Structure\Provider\Privilege\StructurePrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Privilege\Privileges;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => RoleController::class,
                    'action' => [
                        'index',
                        'incrementer-ordre',
                        'decrementer-ordre',
                    ],
                    'roles' => [
                        'Administrateur technique'
                    ],
                ],
                [
                    'controller' => 'Application\Controller\Admin',
                    'action'     => [
                        'index',
                    ],
                    'privileges' => [
                        UtilisateurPrivileges::UTILISATEUR_ATTRIBUTION_ROLE,
                        StructurePrivileges::STRUCTURE_CONSULTATION_TOUTES_STRUCTURES,
                        StructurePrivileges::STRUCTURE_CONSULTATION_SES_STRUCTURES,
                     ],
                ],
                [
                    'controller' => \Application\Controller\PrivilegeController::class,
                    'action'     => [
                        'index',
                    ],
                    'privileges' => [
                        Privileges::DROIT_PRIVILEGE_VISUALISATION,
                        Privileges::DROIT_PRIVILEGE_EDITION,
                    ],
                ],
                [
                    'controller' => \Application\Controller\PrivilegeController::class,
                    'action'     => [
                        'modifier',
                    ],
                    'privileges' => [
                        Privileges::DROIT_PRIVILEGE_EDITION,
                    ],
                ],
                [
                    'controller' => 'UnicaenApp\Controller\Application',
                    'action'     => [
                        'test-envoi-mail',
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_ATTRIBUTION_ROLE,
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'admin' => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/admin',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Admin',
                        'action'        => 'index',
                    ],
                ],
            ],
            'gestion-privilege' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/gestion-privilege',
                    'defaults' => [
                        'controller'    => \Application\Controller\PrivilegeController::class,
                        'action'        => 'index',
                    ],
                ],
            ],
            'role-ordre' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/role-ordre',
                    'defaults' => [
                        'controller'    => RoleController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'incrementer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/incrementer/:role',
                            'defaults' => [
                                'controller'    => RoleController::class,
                                'action'        => 'incrementer-ordre',
                            ],
                        ],
                    ],
                    'decrementer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/decrementer/:role',
                            'defaults' => [
                                'controller'    => RoleController::class,
                                'action'        => 'decrementer-ordre',
                            ],
                        ],
                    ],
                ]
            ],
            'modifier-privilege' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/modifier-privilege/:role/:privilege',
                    'defaults' => [
                        'controller'    => \Application\Controller\PrivilegeController::class,
                        'action'        => 'modifier',
                    ],
                ],
            ],
            'modifier-modele-privilege' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/modifier-modele-privilege/:role/:privilege',
                    'defaults' => [
                        'controller'    => \Application\Controller\PrivilegeController::class,
                        'action'        => 'modifier-modele',
                    ],
                ],
            ],
            'test-envoi-mail' => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/test-envoi-mail',
//                    'defaults' => [
//                        '__NAMESPACE__' => 'Application\Controller',
//                        'controller'    => 'Admin',
//                        'action'        => 'index',
//                    ],
                ],
            ],
        ],
    ],
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'order'    => 100,
                        'label'    => 'Administration',
                        'route'    => 'admin',
                        'resource' => PrivilegeController::getResourceId('Application\Controller\Admin', 'index'),
                        'pages' => [
                            'test-envoi-mail' => [
                                'label'    => 'Test envoi mail',
                                'route'    => 'test-envoi-mail',
                                'icon'     => 'icon icon-notifier',
                                'resource' => PrivilegeController::getResourceId('UnicaenApp\Controller\Application', 'test-envoi-mail'),
                                'order' => 10000,
                            ],
                        ],
                    ],
                    'administration' => [
                        'route'    => 'admin',
                        'visible'  => false,
                    ],
                ],
            ],
        ],
    ],
    'controllers'     => [
        'invokables' => [
            'Application\Controller\Admin' => AdminController::class,

        ],
        'factories' => [
            \Application\Controller\PrivilegeController::class =>  PrivilegeControllerFactory::class,
            RoleController::class => RoleControllerFactory::class,
        ],
    ],

];
