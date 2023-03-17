<?php

use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenRenderer\Controller\IndexController;
use UnicaenRenderer\Controller\MacroController;
use UnicaenRenderer\Controller\RenduController;
use UnicaenRenderer\Controller\TemplateController;
use UnicaenRenderer\Provider\Privilege\DocumentcontenuPrivileges;
use UnicaenRenderer\Provider\Privilege\DocumentmacroPrivileges;
use UnicaenRenderer\Provider\Privilege\DocumenttemplatePrivileges;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => [
                        DocumentcontenuPrivileges::DOCUMENTCONTENU_INDEX,
                        DocumentmacroPrivileges::DOCUMENTMACRO_INDEX,
                        DocumenttemplatePrivileges::DOCUMENTTEMPLATE_INDEX
                    ],
                ],
                [
                    'controller' => MacroController::class,
                    'action' => [
                        'index',
                        'generer-json'
                    ],
                    'privileges' => [
                        DocumentmacroPrivileges::DOCUMENTMACRO_INDEX,
                    ],
                ],
                [
                    'controller' => MacroController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => [
                        DocumentmacroPrivileges::DOCUMENTMACRO_AJOUTER,
                    ],
                ],
                [
                    'controller' => MacroController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privileges' => [
                        DocumentmacroPrivileges::DOCUMENTMACRO_MODIFIER,
                    ],
                ],
                [
                    'controller' => MacroController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => [
                        DocumentmacroPrivileges::DOCUMENTMACRO_SUPPRIMER,
                    ],
                ],
                [
                    'controller' => RenduController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => [
                        DocumentcontenuPrivileges::DOCUMENTCONTENU_INDEX,
                    ],
                ],
                [
                    'controller' => RenduController::class,
                    'action' => [
                        'afficher',
                    ],
                    'privileges' => [
                        DocumentcontenuPrivileges::DOCUMENTCONTENU_AFFICHER,
                    ],
                ],
                [
                    'controller' => RenduController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => [
                        DocumentcontenuPrivileges::DOCUMENTCONTENU_SUPPRIMER,
                    ],
                ],
                [
                    'controller' => TemplateController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => [
                        DocumenttemplatePrivileges::DOCUMENTTEMPLATE_INDEX,
                    ],
                ],
                [
                    'controller' => TemplateController::class,
                    'action' => [
                        'afficher',
                    ],
                    'privileges' => [
                        DocumenttemplatePrivileges::DOCUMENTTEMPLATE_AFFICHER,
                    ],
                ],
                [
                    'controller' => TemplateController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => [
                        DocumenttemplatePrivileges::DOCUMENTTEMPLATE_AJOUTER,
                    ],
                ],
                [
                    'controller' => TemplateController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privileges' => [
                        DocumenttemplatePrivileges::DOCUMENTTEMPLATE_MODIFIER,
                    ],
                ],
                [
                    'controller' => TemplateController::class,
                    'action' => [
                        'detruire',
                    ],
                    'privileges' => [
                        DocumenttemplatePrivileges::DOCUMENTTEMPLATE_SUPPRIMER,
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
                            'contenu' => [
                                'label' => 'Gestion de contenus',
                                'route' => 'contenu',
                                'resource' => PrivilegeController::getResourceId(IndexController::class, 'index'),
                                'order'    => 10002,
                                'icon'     => 'far fa-file-code',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
