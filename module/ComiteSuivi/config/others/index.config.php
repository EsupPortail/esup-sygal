<?php

use Application\Provider\Privilege\SubstitutionPrivileges;
use ComiteSuivi\Controller\IndexController;
use ComiteSuivi\Controller\IndexControllerFactory;
use ComiteSuivi\Form\Membre\MembreForm;
use ComiteSuivi\Form\Membre\MembreFormFactory;
use ComiteSuivi\Form\Membre\MembreHydrator;
use ComiteSuivi\Form\Membre\MembreHydratorFactory;
use ComiteSuivi\Service\Membre\MembreService;
use ComiteSuivi\Service\Membre\MembreServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Http\Literal;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => IndexController::class,
                    'action'     => [
                        'index',
                    ],
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'comite-suivi' => [
                'type'          => Literal::class,
                'options'       => [
                    'route'    => '/comite-suivi',
                    'defaults' => [
                        'controller'    => IndexController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
            ],
        ],
    ],

    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'comite-suivi' => [
                        'label'    => 'Comité de suivi',
                        'route'    => 'comite-suivi',
                        'resource' => PrivilegeController::getResourceId(\ComiteSuivi\Controller\ComiteSuiviController::class, 'index'),
                        'order'    => 50,
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
        ],
    ],
    'controllers'     => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
        ],
    ],
    'form_elements'   => [
        'factories' => [
        ],
    ],
    'hydrators' => [
        'factories' => [
        ],
    ],
    'view_helpers' => [
        'invokables' => [
        ],
        'factories' => [],
    ],
];
