<?php

namespace Application;


use Application\Controller\Factory\IndividuComplControllerFactory;
use Application\Controller\IndividuComplController;
use Application\Form\IndividuCompl\IndividuComplForm;
use Application\Form\IndividuCompl\IndividuComplFormFactory;
use Application\Form\IndividuCompl\IndividuComplHydrator;
use Application\Form\IndividuCompl\IndividuComplHydratorFactory;
use Application\Service\IndividuCompl\IndividuComplService;
use Application\Service\IndividuCompl\IndividuComplServiceFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndividuComplController::class,
                    'action'     => [
                        'index',
                        'afficher',
                        'ajouter',
                        'modifier',
                        'historiser',
                        'restaurer',
                        'detruire',
                    ],
                    'role' => [],
                ],
            ],
        ],
    ],

    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            'individu-compl' => [
                                'label'    => "Compléments d'individu",
                                'route'    => 'individu-compl',
                                'resource' => PrivilegeController::getResourceId(IndividuComplController::class, 'index'),
                                'icon'     => "fas fa-user-edit",
                                'order'    => 65,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'individu-compl' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/individu-compl',
                    'defaults' => [
                        'controller'    => IndividuComplController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'afficher' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/afficher/:individu-compl',
                            'defaults' => [
                                'controller'    => IndividuComplController::class,
                                'action'        => 'afficher',
                            ],
                        ],
                    ],
                    'ajouter' => [
                        'type'          => Literal::class,
                        'options'       => [
                            'route'    => '/ajouter',
                            'defaults' => [
                                'controller'    => IndividuComplController::class,
                                'action'        => 'ajouter',
                            ],
                        ],
                    ],
                    'modifier' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/modifier/:individu-compl',
                            'defaults' => [
                                'controller'    => IndividuComplController::class,
                                'action'        => 'modifier',
                            ],
                        ],
                    ],
                    'historiser' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/historiser/:individu-compl',
                            'defaults' => [
                                'controller'    => IndividuComplController::class,
                                'action'        => 'historiser',
                            ],
                        ],
                    ],
                    'restaurer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/restaurer/:individu-compl',
                            'defaults' => [
                                'controller'    => IndividuComplController::class,
                                'action'        => 'restaurer',
                            ],
                        ],
                    ],
                    'detruire' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/detruire/:individu-compl',
                            'defaults' => [
                                'controller'    => IndividuComplController::class,
                                'action'        => 'detruire',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            IndividuComplController::class => IndividuComplControllerFactory::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
            IndividuComplService::class => IndividuComplServiceFactory::class,
        ]
    ],
    'form_elements'   => [
        'factories' => [
            IndividuComplForm::class => IndividuComplFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            IndividuComplHydrator::class => IndividuComplHydratorFactory::class,
        ],
    ],
];