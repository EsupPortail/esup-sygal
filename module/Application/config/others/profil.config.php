<?php

namespace Application;

use Application\Controller\Factory\ProfilControllerFactory;
use Application\Controller\ProfilController;
use Application\Form\Factory\ProfilFormFactory;
use Application\Form\Factory\ProfilHydratorFactory;
use Application\Form\Hydrator\ProfilHydrator;
use Application\Form\ProfilForm;
use Application\Service\Profil\ProfilService;
use Application\Service\Profil\ProfilServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Privilege\Privileges;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ProfilController::class,
                    'action' => [
                        'index',
                    ],
                    'privileges' => [
                        Privileges::DROIT_PRIVILEGE_VISUALISATION,
                        Privileges::DROIT_PRIVILEGE_EDITION,
                    ],
                ],
                [
                    'controller' => ProfilController::class,
                    'action' => [
                        'editer',
                        'supprimer',
                        'gerer-roles',
                        'ajouter-role',
                        'retirer-role',
                        'dupliquer-privileges',
                        'modifier-profil-privilege'
                    ],
                    'privileges' => [
                        Privileges::DROIT_PRIVILEGE_EDITION,
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'profil' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/profil',
                    'defaults' => [
                        'controller'    => ProfilController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'editer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/editer[/:profil]',
                            'defaults' => [
                                'controller'    => ProfilController::class,
                                'action'        => 'editer',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'supprimer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/supprimer/:profil',
                            'defaults' => [
                                'controller'    => ProfilController::class,
                                'action'        => 'supprimer',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'dupliquer' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/dupliquer/:profil',
                            'defaults' => [
                                'controller'    => ProfilController::class,
                                'action'        => 'dupliquer-privileges',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'gerer-roles' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'    => '/gerer-roles/:profil',
                            'defaults' => [
                                'controller'    => ProfilController::class,
                                'action'        => 'gerer-roles',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'retirer' => [
                                'type'          => Segment::class,
                                'options'       => [
                                    'route'    => '/retirer/:role',
                                    'defaults' => [
                                        'controller'    => ProfilController::class,
                                        'action'        => 'retirer-role',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                            'ajouter' => [
                                'type'          => Literal::class,
                                'options'       => [
                                    'route'    => '/ajouter',
                                    'defaults' => [
                                        'controller'    => ProfilController::class,
                                        'action'        => 'ajouter-role',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'modifier-profil-privilege' => [
                'type'          => Segment::class,
                'options'       => [
                    'route'    => '/modifier-profil-privilege/:profil/:privilege',
                    'defaults' => [
                        'controller'    => ProfilController::class,
                        'action'        => 'modifier-profil-privilege',
                    ],
                ],
                'may_terminate' => true,
            ],
        ],
    ],
    'navigation' => [
    ],
    'service_manager' => [
        'factories' => [
            ProfilService::class => ProfilServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            ProfilController::class => ProfilControllerFactory::class,
        ],
    ],

    'form_elements'   => [
        'factories' => [
            ProfilForm::class => ProfilFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            ProfilHydrator::class => ProfilHydratorFactory::class,
        ]
    ],
];