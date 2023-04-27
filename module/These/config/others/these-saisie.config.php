<?php

namespace These;

use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use These\Controller\Factory\TheseSaisieControllerFactory;
use These\Controller\TheseRechercheController;
use These\Controller\TheseSaisieController;
use These\Form\TheseSaisie\TheseSaisieForm;
use These\Form\TheseSaisie\TheseSaisieFormFactory;
use These\Form\TheseSaisie\TheseSaisieHydrator;
use These\Form\TheseSaisie\TheseSaisieHydratorFactory;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => TheseSaisieController::class,
                    'action' => [
                        'saisie',
                    ],
                    'roles' => 'Administrateur technique',
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'these' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/these',
                    'defaults' => [
                        'controller' => TheseRechercheController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'saisie' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/saisie[/:these]',
                            'defaults' => [
                                'controller' => TheseSaisieController::class,
                                'action' => 'saisie',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
            TheseSaisieController::class => TheseSaisieControllerFactory::class,
        ],
    ],
    'form_elements' => [
        'factories' => [
            TheseSaisieForm::class => TheseSaisieFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
            TheseSaisieHydrator::class => TheseSaisieHydratorFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [],
    ],
    'view_helpers' => [
        'factories' => [],
    ],
];