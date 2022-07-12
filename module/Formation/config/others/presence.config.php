<?php

namespace Formation;

use Formation\Controller\PresenceController;
use Formation\Controller\PresenceControllerFactory;
use Formation\Provider\Privilege\SeancePrivileges;
use Formation\Service\Presence\PresenceService;
use Formation\Service\Presence\PresenceServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => PresenceController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => [
                        SeancePrivileges::SEANCE_PRESENCE,
                    ],
                ],
                [
                    'controller' => PresenceController::class,
                    'action' => [
                        'renseigner-presences',
                    ],
                    'privileges' => [
                        SeancePrivileges::SEANCE_PRESENCE,
                    ],
                ],
                [
                    'controller' => PresenceController::class,
                    'action' => [
                        'toggle-presence',
                        'toggle-presences',
                    ],
                    'privileges' => [
                        SeancePrivileges::SEANCE_PRESENCE,
                    ],
                ],
            ],
        ],
    ],

//    'navigation' => [
//        'default' => [
//            'home' => [
//                'pages' => [
//                    'formation' => [
//                        'pages' => [
//                            'presence' => [
//                                'label'    => 'Presences',
//                                'route'    => 'formation/presence',
//                                'resource' => PrivilegeController::getResourceId(PresenceController::class, 'index') ,
//                                'order'    => 500,
//                            ],
//                        ],
//                    ],
//                ],
//            ],
//        ],
//    ],

    'router'          => [
        'routes' => [
            'formation' => [
                'child_routes' => [
                    'presence' => [
                        'type'  => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/presence',
                            'defaults' => [
                                'controller' => PresenceController::class,
                                'action'     => 'index',
                            ],
                        ],
                        'child_routes' => [
                            'renseigner-presences' => [
                                'type'  => Segment::class,
                                'options' => [
                                    'route'    => '/renseigner-presences/:session',
                                    'defaults' => [
                                        'controller' => PresenceController::class,
                                        'action'     => 'renseigner-presences',
                                    ],
                                ],
                            ],
                            'toggle-presence' => [
                                'type'  => Segment::class,
                                'options' => [
                                    'route'    => '/toggle-presence/:seance/:inscription',
                                    'defaults' => [
                                        'controller' => PresenceController::class,
                                        'action'     => 'toggle-presence',
                                    ],
                                ],
                            ],
                            'toggle-presences' => [
                                'type'  => Segment::class,
                                'options' => [
                                    'route'    => '/toggle-presences/:mode/:inscription',
                                    'defaults' => [
                                        'controller' => PresenceController::class,
                                        'action'     => 'toggle-presences',
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
            PresenceService::class => PresenceServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            PresenceController::class => PresenceControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [],
    ],
    'hydrators' => [
        'factories' => [],
    ]

];