<?php

use ComiteSuivi\Form\Membre\MembreForm;
use ComiteSuivi\Form\Membre\MembreFormFactory;
use ComiteSuivi\Form\Membre\MembreHydrator;
use ComiteSuivi\Form\Membre\MembreHydratorFactory;
use ComiteSuivi\Service\Membre\MembreService;
use ComiteSuivi\Service\Membre\MembreServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
            ],
        ],
    ],
    'router'          => [
        'routes' => [
        ],
    ],

    'navigation'      => [
        'default' => [
            'home' => [
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            MembreService::class =>  MembreServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
        ],
    ],
    'form_elements'   => [
        'factories' => [
            MembreForm::class => MembreFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            MembreHydrator::class => MembreHydratorFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
        ],
        'factories' => [],
    ],
];
