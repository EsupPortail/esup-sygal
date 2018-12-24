<?php

use Application\Entity\Db\Privilege;
use Application\Entity\Db\Role;
use Application\Service\Role\RoleService;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Privilege\Privileges;

return [
    'unicaen-auth' => [
        /**
         * Classes représentant les entités rôle et privilège.
         * - Entité rôle      : héritant de \UnicaenAuth\Entity\Db\AbstractRole      ou implémentant \UnicaenAuth\Entity\Db\RoleInterface.
         * - Entité privilège : héritant de \UnicaenAuth\Entity\Db\AbstractPrivilege ou implémentant \UnicaenAuth\Entity\Db\PrivilegeInterface.
         *
         * Valeurs par défaut :
         * - 'role_entity_class'      : 'UnicaenAuth\Entity\Db\Role'
         * - 'privilege_entity_class' : 'UnicaenAuth\Entity\Db\Privilege'
         */
        'role_entity_class'      => Role::class,
        'privilege_entity_class' => Privilege::class,
    ],

    'bjyauthorize' => [
        'resource_providers' => [
            /**
             * Le service Privilèges peut aussi être une source de ressources,
             * si on souhaite tester directement l'accès à un privilège
             */
            'UnicaenAuth\Service\Privilege' => [],
        ],

        'rule_providers'     => [
            'UnicaenAuth\Provider\Rule\PrivilegeRuleProvider' => [],
        ],

        'guards' => [
            'UnicaenAuth\Guard\PrivilegeController' => [
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action'     => ['index'],
                    'privileges' => [
                        Privileges::DROIT_ROLE_VISUALISATION,
                        Privileges::DROIT_PRIVILEGE_VISUALISATION,
                    ],
                ],
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action'     => ['roles'],
                    'privileges' => [Privileges::DROIT_ROLE_VISUALISATION],
                ],
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action'     => ['privileges'],
                    'privileges' => [Privileges::DROIT_PRIVILEGE_VISUALISATION],
                ],
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action'     => ['role-edition', 'role-suppression'],
                    'privileges' => [Privileges::DROIT_ROLE_EDITION],
                ],
                [
                    'controller' => 'UnicaenAuth\Controller\Droits',
                    'action'     => ['privileges-modifier'],
                    'privileges' => [Privileges::DROIT_PRIVILEGE_EDITION],
                ],
            ],
        ],
    ],

    'navigation'   => [
        // The DefaultNavigationFactory we configured uses 'default' as the sitemap key
        'default' => [
            // And finally, here is where we define our page hierarchy
            'home' => [
                'pages' => [
                    'droits' => [
                        'label'    => 'Droits d\'accès',
                        'title'    => 'Gestion des droits d\'accès',
                        'route'    => 'droits',
                        'resource' => PrivilegeController::getResourceId('UnicaenAuth\Controller\Droits', 'index'),
                        'pages'    => [
                            'roles'      => [
                                'label'      => "Rôles",
                                'title'      => "Gestion des rôles",
                                'route'      => 'droits/roles',
                                'resource'   => PrivilegeController::getResourceId('UnicaenAuth\Controller\Droits', 'roles'),
                                'withtarget' => true,
                            ],
                            'privileges' => [
                                'label'      => "Privilèges",
                                'title'      => "Gestion des privilèges",
                                'route'      => 'gestion-privilege',
                                'resource'   => PrivilegeController::getResourceId('UnicaenAuth\Controller\Droits', 'privileges'),
                                'withtarget' => true,
                            ],
                            'role-modele' => [
                                'label'      => "Rôles modèles",
                                'title'      => "Gestion des rôles modèles",
                                'route'      => 'gestion-role-modele',
                                'resource'   => PrivilegeController::getResourceId('UnicaenAuth\Controller\Droits', 'privileges'),
                                'withtarget' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [
            RoleService::class => RoleService::class,
        ],
        'factories' => [
        ],
    ],

    'controllers'     => [
        'invokables' => [
        ],
        'factories' => [
        ],
    ],
];
