<?php

namespace Formation;


use Formation\Controller\EnqueteController;
use Formation\Controller\EnqueteControllerFactory;
use Formation\Form\EnqueteReponse\EnqueteReponseForm;
use Formation\Form\EnqueteReponse\EnqueteReponseFormFactory;
use Formation\Form\EnqueteReponse\EnqueteReponseHydrator;
use Formation\Form\EnqueteReponse\EnqueteReponseHydratorFactory;
use Formation\Provider\Privilege\IndexPrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => EnqueteController::class,
                    'action' => [
                        'afficher-questions',
                        'repondre-questions',
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
                            'enquete' => [
                                'label'    => 'Enquête',
                                'route'    => 'formation/enquete/questions',
                                'resource' => PrivilegeController::getResourceId(EnqueteController::class, 'afficher-questions') ,
                                'order'    => 700,
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
                    'enquete' => [
                        'type'  => Literal::class,
                        'may_terminate' => false,
                        'options' => [
                            'route'    => '/enquete',
                        ],
                        'child_routes' => [
                            'questions' => [
                                'type'  => Literal::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/questions',
                                    'defaults' => [
                                        'controller' => EnqueteController::class,
                                        'action'     => 'afficher-questions',
                                    ],
                                ],
                            ],
                            'repondre-questions' => [
                                'type'  => Segment::class,
                                'may_terminate' => true,
                                'options' => [
                                    'route'    => '/repondre-questions/:inscription',
                                    'defaults' => [
                                        'controller' => EnqueteController::class,
                                        'action'     => 'repondre-questions',
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
        'factories' => [],
    ],
    'controllers'     => [
        'factories' => [
            EnqueteController::class => EnqueteControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            EnqueteReponseForm::class => EnqueteReponseFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            EnqueteReponseHydrator::class => EnqueteReponseHydratorFactory::class,
        ],
    ]

];