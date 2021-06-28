<?php

namespace Formation;

use Formation\Controller\InscriptionController;
use Formation\Controller\InscriptionControllerFactory;
use Formation\Provider\Privilege\IndexPrivileges;
use Formation\Service\Inscription\InscriptionService;
use Formation\Service\Inscription\InscriptionServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => InscriptionController::class,
                    'action' => [
                        'index',
                        'ajouter',
                    ],
                    'privileges' => [
                        IndexPrivileges::INDEX_AFFICHER,
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
                            'inscription' => [
                                'label'    => 'Inscriptions',
                                'route'    => 'formation/inscription',
                                'resource' => PrivilegeController::getResourceId(InscriptionController::class, 'index') ,
                                'order'    => 400,
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
                    'inscription' => [
                        'type'  => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/inscription',
                            'defaults' => [
                                'controller' => InscriptionController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'ajouter' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/ajouter/:session[/:individu]',
                                    'defaults' => [
                                        'controller' => InscriptionController::class,
                                        'action'     => 'ajouter',
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
            InscriptionService::class => InscriptionServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            InscriptionController::class => InscriptionControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [],
    ],
    'hydrators' => [
        'factories' => [],
    ]

];