<?php

namespace These;

use These\Controller\CoEncadrantController;
use These\Controller\Factory\PresidentJuryControllerFactory;
use These\Controller\PresidentJuryController;
use Application\Form\AdresseMail\AdresseMailForm;
use Application\Form\AdresseMail\AdresseMailFormFactory;
use Application\Form\AdresseMail\AdresseMailHydrator;
use These\Provider\Privilege\PresidentJuryPrivileges;
use UnicaenAuth\Guard\PrivilegeController;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => PresidentJuryController::class,
                    'action'     => [
                        'index',
                    ],
                    'privileges' => [
                        PresidentJuryPrivileges::PRESIDENT_GESTION
                    ],
                ],
                [
                    'controller' => PresidentJuryController::class,
                    'action'     => [
                        'ajouter-mail',
                        'supprimer-mail',
                    ],
                    'privileges' => [
                        PresidentJuryPrivileges::PRESIDENT_MODIFIER_MAIL,
                    ],
                ],
                [
                    'controller' => PresidentJuryController::class,
                    'action'     => [
                        'notifier-correction',
                    ],
                    'privileges' => [
                        PresidentJuryPrivileges::PRESIDENT_NOTIFIER,
                    ],
                ],
            ],
        ],
    ],

    'router'          => [
        'routes' => [
            'president-jury' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/president-jury',
                    'defaults' => [
                        'controller'    => PresidentJuryController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes'  => [
                    'notifier-correction' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/notifier-correction/:president',
                            'defaults' => [
                                'controller'    => PresidentJuryController::class,
                                'action'        => 'notifier-correction',
                            ],
                        ],
                    ],
                    'ajouter-mail' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/ajouter-mail/:president',
                            'defaults' => [
                                'controller'    => PresidentJuryController::class,
                                'action'        => 'ajouter-mail',
                            ],
                        ],
                    ],
                    'supprimer-mail' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/supprimer-mail/:president',
                            'defaults' => [
                                'controller'    => PresidentJuryController::class,
                                'action'        => 'supprimer-mail',
                            ],
                        ],
                    ],
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
                            'president-jury' => [
                                'label'    => 'Présidents du jury',
                                'route'    => 'president-jury',
                                'resource' => PrivilegeController::getResourceId(CoEncadrantController::class, 'index'),
                                'order'    => 300,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            PresidentJuryController::class => PresidentJuryControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            AdresseMailForm::class => AdresseMailFormFactory::class,
        ],
    ],
    'hydrators' => [
        'invokables' => [
            AdresseMailHydrator::class => AdresseMailHydrator::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
        ],
    ],
];
