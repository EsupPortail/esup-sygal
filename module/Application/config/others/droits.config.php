<?php

use Application\Controller\DroitsController;
use Application\Controller\Factory\DroitsControllerFactory;
use Application\Controller\ProfilController;
use Application\Entity\Db\Privilege;
use Application\Entity\Db\Role;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Privilege\PrivilegePrivileges;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;
use UnicaenPrivilege\Service\Privilege\PrivilegeService;
use UnicaenUtilisateur\Controller\RoleController;
use UnicaenUtilisateur\Provider\Privilege\RolePrivileges;


return [
    'unicaen-auth' => [
        /**
         * Classes représentant les entités rôle et privilège.
         * - Entité rôle      : héritant de \UnicaenUtilisateur\Entity\Db\AbstractRole      ou implémentant \UnicaenUtilisateur\Entity\Db\RoleInterface.
         * - Entité privilège : héritant de \UnicaenPrivilege\Entity\Db\AbstractPrivilege ou implémentant \UnicaenPrivilege\Entity\Db\PrivilegeInterface.
         *
         * Valeurs par défaut :
         * - 'role_entity_class'      : 'UnicaenUtilisateur\Entity\Db\Role'
         * - 'privilege_entity_class' : 'UnicaenPrivilege\Entity\Db\Privilege'
         */
        'role_entity_class' => Role::class,
        'privilege_entity_class' => Privilege::class,
    ],

    'bjyauthorize' => [
        'resource_providers' => [
            /**
             * Le service Privilèges peut aussi être une source de ressources,
             * si on souhaite tester directement l'accès à un privilège
             */
            PrivilegeService::class => [],
        ],

        'rule_providers' => [
            PrivilegeRuleProvider::class => [],
        ],

        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => DroitsController::class,
                    'action' => ['index'],
                    'privileges' => [
                        RolePrivileges::ROLE_AFFICHER,
                        PrivilegePrivileges::PRIVILEGE_VOIR,
                    ],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'droits' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/droits',
                    'defaults' => [
                        'controller' => DroitsController::class,
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'droits' => [
                                'label' => "Droits d'accès",
                                'title' => "Gestion des droits d'accès",
                                'route' => 'droits',
                                'resource' => PrivilegeController::getResourceId(RoleController::class, 'index'),
                                'icon' => 'fas fa-user-lock',
                                'order' => 0,
                                'pages' => [
                                    'roles' => [
                                        'label' => "Rôles",
                                        'title' => "Gestion des rôles",
                                        'route' => 'unicaen-role',
                                        'resource' => PrivilegeController::getResourceId(RoleController::class, 'index'),
                                        'withtarget' => true,
                                    ],
                                    'profil' => [
                                        'label' => "Profils et privilèges",
                                        'title' => "Profils et privilèges",
                                        'route' => 'profil',
                                        'query' => ['depend' => ProfilController::PERIMETRE_Aucun],
                                        'resource' => PrivilegeController::getResourceId(ProfilController::class, 'index'),
                                        'withtarget' => true,
                                    ],
                                    'privileges' => [
                                        'label' => "Rôles et privilèges",
                                        'title' => "Rôles et privilèges",
                                        'route' => 'gestion-privilege',
                                        'query' => ['depend' => \Application\Controller\PrivilegeController::PERIMETRE_Aucun],
                                        'resource' => PrivilegeController::getResourceId(\Application\Controller\PrivilegeController::class, 'index'),
                                        'withtarget' => true,
                                    ],
                                ],
                            ],
                            '---------------------droits-divider' => [
                                'label' => null,
                                'order' => 0,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            DroitsController::class => DroitsControllerFactory::class,
        ],
    ],

    'view_manager' => [
        'template_map' => [
            'unicaen-utilisateur/role/index' => __DIR__ . '/../../view/application/droits/roles.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
