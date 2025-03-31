<?php

namespace UnicaenParametre;

use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenParametre\Controller\CategorieController;
use UnicaenParametre\Controller\ParametreController;
use UnicaenParametre\Provider\Privilege\ParametrecategoriePrivileges;
use UnicaenParametre\Provider\Privilege\ParametrePrivileges;

return [

    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => CategorieController::class,
                    'action' => [
                        'index',
                    ],
                    'pivileges' => ParametrecategoriePrivileges::PARAMETRECATEGORIE_INDEX,
                ],
                [
                    'controller' => CategorieController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => ParametrecategoriePrivileges::PARAMETRECATEGORIE_AJOUTER,
                ],
                [
                    'controller' => CategorieController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privileges' => ParametrecategoriePrivileges::PARAMETRECATEGORIE_MODIFIER,
                ],
                [
                    'controller' => CategorieController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => ParametrecategoriePrivileges::PARAMETRECATEGORIE_SUPPRIMER,
                ],
                [
                    'controller' => ParametreController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => ParametrePrivileges::PARAMETRE_AJOUTER,
                ],
                [
                    'controller' => ParametreController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privileges' => ParametrePrivileges::PARAMETRE_MODIFIER,
                ],
                [
                    'controller' => ParametreController::class,
                    'action' => [
                        'modifier-valeur',
                    ],
                    'privileges' => ParametrePrivileges::PARAMETRE_VALEUR,
                ],
                [
                    'controller' => ParametreController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => ParametrePrivileges::PARAMETRE_SUPPRIMER,
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
                            'parametres' => [
                                'label' => 'Gestion des paramètres',
                                'route' => 'parametre/index',
                                'resource' => PrivilegeController::getResourceId(CategorieController::class, 'index'),
                                'order'    => 10001,
                                'icon'     => 'fas fa-tools',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];