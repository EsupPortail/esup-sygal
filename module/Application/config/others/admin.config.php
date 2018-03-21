<?php

use Application\Controller\Factory\RoleControllerFactory;
use Application\Controller\AdminController;
use Application\Provider\Privilege\UtilisateurPrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;
use UnicaenAuth\Provider\Privilege\Privileges;
return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Admin',
                    'action'     => [
                        'index',
                    ],
                    'privileges' => UtilisateurPrivileges::UTILISATEUR_ATTRIBUTION_ROLE,
                ],
                [
                    'controller' => 'Application\Controller\Role',
                    'action'     => [
                        'index',
                    ],
                    'privileges' => [
                        Privileges::DROIT_PRIVILEGE_VISUALISATION,
                        Privileges::DROIT_PRIVILEGE_EDITION,
                    ],
                ],
                [
                    'controller' => 'Application\Controller\Role',
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
                    'route'    => '/[:language/]admin',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Admin',
                        'action'        => 'index',
                    ],
                ],
            ],
            'roles' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/roles',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Role',
                        'action'        => 'index',
                    ],
                ],
            ],
            'modifier-privilege' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/modifier-privilege/:role/:privilege',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Role',
                        'action'        => 'modifier',
                    ],
                ],
            ],
            'test-envoi-mail' => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/[:language/]test-envoi-mail',
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
                        'order'    => -90,
                        'label'    => 'Administration',
                        'route'    => 'admin',
                        'icon'     => 'glyphicon glyphicon-cog',
                        'resource' => PrivilegeController::getResourceId('Application\Controller\Admin', 'index'),
                        'pages' => [
                            'test-envoi-mail' => [
                                'label'    => 'Test envoi mail',
                                'route'    => 'test-envoi-mail',
                                //'icon'     => 'glyphicon glyphicon-send',
                                'resource' => PrivilegeController::getResourceId('UnicaenApp\Controller\Application', 'test-envoi-mail'),
                                'withtarget' => true,
                                'paramsInject' => [
                                    'language',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => array(
        ),
        'factories' => [
        ],
    ],
    'controllers'     => [
        'invokables' => [
            'Application\Controller\Admin' => AdminController::class,
        ],
        'factories' => [
            'Application\Controller\Role' => RoleControllerFactory::class,
        ],
    ],
];
