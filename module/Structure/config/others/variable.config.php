<?php

use Application\Service\Variable\VariableService;
use Application\Service\Variable\VariableServiceFactory;
use Laminas\Router\Http\Segment;
use Structure\Controller\Factory\VariableControllerFactory;
use Structure\Controller\VariableController;
use Structure\Form\Factory\VariableFormFactory;
use Structure\Form\VariableForm;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => VariableController::class,
                    'action'     => [
                        'saisir-variable',
                        'supprimer',
                    ],
                    'role' => [],
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'etablissement' => [
                'child_routes'  => [
                    'saisir-variable' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/saisir-variable[/:id]/:etablissement',
                            'constraints' => [
                                'id' => '\d+',
                                'etablissement' => '\d+',
                            ],
                            'defaults'    => [
                                'controller'    => VariableController::class,
                                'action'        => 'saisir-variable',
                            ],
                        ],
                    ],
                    'supprimer-variable' => [
                        'type'          => Segment::class,
                        'options'       => [
                            'route'       => '/supprimer/:id',
                            'constraints' => [
                                'id' => '\d+',
                            ],
                            'defaults'    => [
                                'controller'    => VariableController::class,
                                'action'        => 'supprimer',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'form_elements'   => [
        'factories' => [
            VariableForm::class => VariableFormFactory::class,
        ],
    ],
    'hydrators' => [
        'factories' => [
        ]
    ],
    'service_manager' => [
        'invokables' => [
        ],
        'factories' => [
            VariableService::class => VariableServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            VariableController::class => VariableControllerFactory::class,
        ],
    ],
];
