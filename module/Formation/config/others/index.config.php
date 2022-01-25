<?php

namespace Formation;

use Formation\Controller\IndexController;
use Formation\Controller\IndexControllerFactory;
use Formation\Provider\Privilege\IndexPrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

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
                        IndexPrivileges::INDEX_AFFICHER,
                    ],
                ],
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index-doctorant',
                    ],
                    'privileges' => [
                        IndexPrivileges::INDEX_DOCTORANT,
                    ],
                ],
                [
                    'controller' => IndexController::class,
                    'action' => [
                        'index-formateur',
                    ],
                    'privileges' => [
                        IndexPrivileges::INDEX_FORMATEUR,
                    ],
                ],
            ],
        ],
    ],

    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'formation' => [
                        'label'    => 'Formations',
                        'route'    => 'formation',
                        'resource' => PrivilegeController::getResourceId(IndexController::class, 'index') ,
                        'order'    => 90,
                    ],
                    'formation-doctorant' => [
                        'label'    => 'Mes formations',
                        'route'    => 'formation/index-doctorant',
                        'resource' => PrivilegeController::getResourceId(IndexController::class, 'index-doctorant') ,
                        'order'    => 91,
                    ],
                    'formation-formateur' => [
                        'label'    => 'Mes formations',
                        'route'    => 'formation/index-formateur',
                        'resource' => PrivilegeController::getResourceId(IndexController::class, 'index-formateur') ,
                        'order'    => 92,
                    ],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'formation' => [
                'type'  => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/formation',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes' => [
                    'index-doctorant' => [
                        'type'  => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/index-doctorant[/:doctorant]',
                            'defaults' => [
                                'controller' => IndexController::class,
                                'action'     => 'index-doctorant',
                            ],
                        ],
                    ],
                    'index-formateur' => [
                        'type'  => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/index-formateur[/:formateur]',
                            'defaults' => [
                                'controller' => IndexController::class,
                                'action'     => 'index-formateur',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [],
    ],
    'controllers'     => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [],
    ],
    'hydrators' => [
        'factories' => [],
    ]

];