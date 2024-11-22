<?php

use Application\Controller\AdminController;
use Application\Controller\Factory\PrivilegeControllerFactory;
use Application\Controller\Factory\RoleControllerFactory;
use Application\Controller\RoleController;
use Application\Provider\Privilege\UtilisateurPrivileges;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Structure\Provider\Privilege\StructurePrivileges;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Privilege\Privileges;

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
                        'ajouter',
                        'editer',
                        'supprimer',
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
                        \UnicaenPrivilege\Provider\Privilege\PrivilegePrivileges::PRIVILEGE_VOIR,
                        \UnicaenPrivilege\Provider\Privilege\PrivilegePrivileges::PRIVILEGE_AFFECTER,
                    ],
                ],
                [
                    'controller' => \Application\Controller\PrivilegeController::class,
                    'action'     => [
                        'modifier',
                    ],
                    'privileges' => [
                        \UnicaenPrivilege\Provider\Privilege\PrivilegePrivileges::PRIVILEGE_MODIFIER,
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
            'role' => [
                'type'          => 'Segment',
                'options'       => [
                    'route'    => '/role',
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'ajouterRole' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/ajouter',
                            'defaults' => [
                                'controller'    => RoleController::class,
                                'action'        => 'ajouter',
                            ],
                        ],
                    ],
                    'editerRole' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/editer/:role',
                            'defaults' => [
                                'controller'    => RoleController::class,
                                'action'        => 'editer',
                            ],
                        ],
                    ],
                    'supprimerRole' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/supprimer/:role',
                            'defaults' => [
                                'controller'    => RoleController::class,
                                'action'        => 'supprimer',
                            ],
                        ],
                    ],
                ]
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
                                'icon'     => 'fas fa-envelope',
                                'resource' => PrivilegeController::getResourceId('UnicaenApp\Controller\Application', 'test-envoi-mail'),
                                'order' => 820,
                            ],
                            '----------mails-divider' => [
                                'label' => null,
                                'order' => 830,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                        ],
                    ],
//                    'administration' => [
//                        'route' => "unicaen-privilege",
//                        'visible'  => false,
//                    ],
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
