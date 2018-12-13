<?php

use Information\Controller\IndexController;
use Information\Controller\IndexControllerFactory;
use Zend\Navigation\Service\NavigationAbstractServiceFactory;

return [
    'bjyauthorize'    => [
        'guards' => [
            \UnicaenAuth\Guard\PrivilegeController::class => [
                [
                    'controller' => IndexController::class,
                    'roles' => [],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'information' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/information',
                    'defaults' => [
                        'controller' => IndexController::class,
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'doctorat' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/doctorat',
                            'defaults' => [
                                'action' => 'doctorat',
                            ],
                        ],
                    ],
                    'ecoles-doctorales' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/ecoles-doctorales',
                            'defaults' => [
                                'action' => 'ecoles-doctorales',
                            ],
                        ],
                    ],
                    'guide-these' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/guide-these',
                            'defaults' => [
                                'action' => 'guide-these',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation'      => [
        'information' => [
            'accueil' => [
                'label' => 'Accueil',
                'route' => 'home',
                'pages' => [
                    'doctorat' => [
                        'label' => 'Le doctorat',
                        'route' => 'information/doctorat',
                        'title' => "Informations sur le doctorat et sa gestion"
                    ],
                    'ecoles-doctorales' => [
                        'label' => 'Les Ecoles Doctorales',
                        'route' => 'information/ecoles-doctorales',
                        'title' => "Informations sur les Ecoles Doctorales et le Collège des Ecoles doctorales"
                    ],
                    'guide-these' => [
                        'label' => 'Guide de la thèse',
                        'route' => 'information/guide-these',
                        'title' => "Informations sur le déroulement de la thèse et formulaires administratifs à l’intention du doctorant et de ses encadrants"
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            NavigationAbstractServiceFactory::class,
        ],
    ],

    'controllers' => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];