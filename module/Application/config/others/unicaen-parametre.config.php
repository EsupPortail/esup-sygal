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
                    'pivileges' => ParametrecategoriePrivileges::PARAMETRECATEGORIE_AJOUTER,
                ],
                [
                    'controller' => CategorieController::class,
                    'action' => [
                        'modifier',
                    ],
                    'pivileges' => ParametrecategoriePrivileges::PARAMETRECATEGORIE_MODIFIER,
                ],
                [
                    'controller' => CategorieController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'pivileges' => ParametrecategoriePrivileges::PARAMETRECATEGORIE_SUPPRIMER,
                ],
                [
                    'controller' => ParametreController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'pivileges' => ParametrePrivileges::PARAMETRE_AJOUTER,
                ],
                [
                    'controller' => ParametreController::class,
                    'action' => [
                        'modifier',
                    ],
                    'pivileges' => ParametrePrivileges::PARAMETRE_MODIFIER,
                ],
                [
                    'controller' => ParametreController::class,
                    'action' => [
                        'modifier-valeur',
                    ],
                    'pivileges' => ParametrePrivileges::PARAMETRE_VALEUR,
                ],
                [
                    'controller' => ParametreController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'pivileges' => ParametrePrivileges::PARAMETRE_SUPPRIMER,
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