<?php

namespace Formation;

use Formation\Controller\FormationController;
use Formation\Controller\ModuleController;
use Formation\Controller\ModuleControllerFactory;
use Formation\Controller\Recherche\ModuleRechercheController;
use Formation\Controller\Recherche\ModuleRechercheControllerFactory;
use Formation\Form\Module\ModuleForm;
use Formation\Form\Module\ModuleFormFactory;
use Formation\Form\Module\ModuleHydrator;
use Formation\Form\Module\ModuleHydratorFactory;
use Formation\Provider\Privilege\ModulePrivileges;
use Formation\Service\Module\ModuleService;
use Formation\Service\Module\ModuleServiceFactory;
use Formation\Service\Module\Search\ModuleSearchService;
use Formation\Service\Module\Search\ModuleSearchServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ModuleController::class,
                    'action' => [
                        'catalogue',
                    ],
                    'privileges' => [
                        ModulePrivileges::MODULE_CATALOGUE,
                    ],
                ],
                [
                    'controller' => ModuleRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                    ],
                    'privileges' => [
                        ModulePrivileges::MODULE_INDEX,
                    ],
                ],
                [
                    'controller' => ModuleController::class,
                    'action' => [
                        'afficher',
                    ],
                    'privileges' => [
                        ModulePrivileges::MODULE_AFFICHER,
                    ],
                ],
                [
                    'controller' => ModuleController::class,
                    'action' => [
                        'ajouter',
                    ],
                    'privileges' => [
                        ModulePrivileges::MODULE_AJOUTER,
                    ],
                ],
                [
                    'controller' => ModuleController::class,
                    'action' => [
                        'modifier',
                    ],
                    'privileges' => [
                        ModulePrivileges::MODULE_MODIFIER,
                    ],
                ],
                [
                    'controller' => ModuleController::class,
                    'action' => [
                        'historiser',
                        'restaurer',
                    ],
                    'privileges' => [
                        ModulePrivileges::MODULE_HISTORISER,
                    ],
                ],
                [
                    'controller' => ModuleController::class,
                    'action' => [
                        'supprimer',
                    ],
                    'privileges' => [
                        ModulePrivileges::MODULE_SUPPRIMER,
                    ],
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    'formation' => [
                        'pages' => [
                            'catalogue' => [
                                'label'    => 'Catalogue des formations',
                                'route'    => 'formation/catalogue',
                                'resource' => PrivilegeController::getResourceId(ModuleController::class, 'catalogue') ,
                                'order'    => 0,
                            ],
                            'module' => [
                                'label'    => 'Modules',
                                'route'    => 'formation/module',
                                'resource' => PrivilegeController::getResourceId(ModuleRechercheController::class, 'index') ,
                                'order'    => 100,
                                'pages' => [
                                    'afficher' => [
                                        'label'    => "Affichage d'un module de formation",
                                        'route'    => 'formation/module/afficher',
                                        'resource' => PrivilegeController::getResourceId(ModuleController::class, 'afficher') ,
                                        'order'    => 100,
                                        'visible' => false,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'formation' => [
                'child_routes' => [
                    'catalogue' => [
                        'type'  => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/catalogue',
                            'defaults' => [
                                'controller' => ModuleController::class,
                                'action'     => 'catalogue',
                            ],
                        ],
                    ],
                    'module' => [
                        'type'  => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/module',
                            'defaults' => [
                                'controller' => ModuleRechercheController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'filters' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
                            'afficher' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/afficher/:module',
                                    'defaults' => [
                                        'controller' => ModuleController::class,
                                        'action'     => 'afficher',
                                    ],
                                ],
                            ],
                            'ajouter' => [
                                'type'  => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter',
                                    'defaults' => [
                                        'controller' => ModuleController::class,
                                        'action'     => 'ajouter',
                                    ],
                                ],
                            ],
                            'modifier' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/modifier/:module',
                                    'defaults' => [
                                        'controller' => ModuleController::class,
                                        'action'     => 'modifier',
                                    ],
                                ],
                            ],
                            'historiser' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/historiser/:module',
                                    'defaults' => [
                                        'controller' => ModuleController::class,
                                        'action'     => 'historiser',
                                    ],
                                ],
                            ],
                            'restaurer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/restaurer/:module',
                                    'defaults' => [
                                        'controller' => ModuleController::class,
                                        'action'     => 'restaurer',
                                    ],
                                ],
                            ],
                            'supprimer' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/supprimer/:module',
                                    'defaults' => [
                                        'controller' => ModuleController::class,
                                        'action'     => 'supprimer',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            ModuleService::class => ModuleServiceFactory::class,
            ModuleSearchService::class => ModuleSearchServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            ModuleController::class => ModuleControllerFactory::class,
            ModuleRechercheController::class => ModuleRechercheControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            ModuleForm::class => ModuleFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            ModuleHydrator::class => ModuleHydratorFactory::class,
        ],
    ]

];