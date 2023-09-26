<?php

namespace Admission;


use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Admission\Controller\AdmissionController;
use UnicaenAuth\Guard\PrivilegeController;
use Laminas\ServiceManager\Factory\InvokableFactory;

return array(
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => AdmissionController::class,
                    'action' => [
                        'etudiant',
                        'inscription',
                        'financement',
                        'justificatifs',
                        'addInformationsEtudiant',
                        'addInformationsInscription',
                        'addInformationsFinancement',
                        'addInformationsJustificatifs'
                    ]
                ]
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'admission' => [
                'type' => Literal::class,
                'may_terminate' => false,
                'options' => [
                    'route' => '/admission',
                ],
                'child_routes' => [
                    'infos-etudiant' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/informations-etudiant',
                            'defaults' => [
                                'controller' => AdmissionController::class,
                                'action' => 'etudiant',
//                                'action' => "addInformationsEtudiant"
                            ],
                        ],
                    ],
                    'infos-inscription' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/informations-inscription',
                            'defaults' => [
                                'controller' => AdmissionController::class,
                                'action' => 'inscription',
//                                'action' => "addInformationsInscription"
                            ],
                        ],
                    ],
                    'infos-financement' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/informations-financement',
                            'defaults' => [
                                'controller' => AdmissionController::class,
                                'action' => 'financement',
//                                'action' => "addInformationsFinancement"
                            ],
                        ],
                    ],
                    'infos-justificatifs' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/informations-justificatifs',
                            'defaults' => [
                                'controller' => AdmissionController::class,
                                'action' => 'justificatifs',
//                                'action' => "addInformationsJustificatifs"
                            ],
                        ],
                    ],
                ],
            ],

        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\AdmissionController::class => Controller\AdmissionControllerFactory::class,
        ],
    ],

    'service_manager' => [
        'factories' => [
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'public_files' => [
        'inline_scripts' => [
        ],
        'stylesheets' => [
            '080_admission' => '/css/admission.css',
        ],
    ],
);
