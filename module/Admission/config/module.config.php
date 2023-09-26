<?php

namespace Admission;


use Admission\Controller\AdmissionControllerFactory;
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
                        'index',
                        'ajouter',
                        'etudiant',
                        'ajouterInscription',
                        'ajouterFinancement',
                        'ajouterJustificatifs',
                        'addInformationsEtudiant',
                        'addInformationsInscription',
                        'addInformationsFinancement',
                        'addInformationsJustificatifs'
                    ],
                    'role' => [],
                    'roles' => [],
                ]
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'admission' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route' => '/admission',
                    'defaults' => [
                        'action' => 'ajouter',
                        'controller' => AdmissionController::class,
                    ],
                ],
                'child_routes' => [
                    'ajouter' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/ajouter/:action',
                            'constraints' => [
                                /**
                                 * @see AdmissionController::ajouterAction()
                                 * @see AdmissionController::ajouterEtudiantAction()
                                 */
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
                            ],
                            'defaults' => [
                                'controller' => AdmissionController::class,
//
//                                'action' => "addInformationsEtudiant"
                            ],
                        ],
                    ],
//                    'infos-etudiant' => [
//                        'type' => Literal::class,
//                        'may_terminate' => true,
//                        'options' => [
//                            'route' => '/informations-etudiant',
//                            'defaults' => [
//                                'controller' => AdmissionController::class,
//                                'action' => "addInformationsEtudiant"
//                            ],
//                        ],
//                    ],
//                    'infos-inscription' => [
//                        'type' => Literal::class,
//                        'may_terminate' => true,
//                        'options' => [
//                            'route' => '/informations-inscription',
//                            'defaults' => [
//                                'controller' => AdmissionController::class,
////                                'action' => 'inscription',
//                                'action' => "addInformationsInscription"
//                            ],
//                        ],
//                    ],
//                    'infos-financement' => [
//                        'type' => Literal::class,
//                        'may_terminate' => true,
//                        'options' => [
//                            'route' => '/informations-financement',
//                            'defaults' => [
//                                'controller' => AdmissionController::class,
////                                'action' => 'financement',
//                                'action' => "addInformationsFinancement"
//                            ],
//                        ],
//                    ],
//                    'infos-justificatifs' => [
//                        'type' => Literal::class,
//                        'may_terminate' => true,
//                        'options' => [
//                            'route' => '/informations-justificatifs',
//                            'defaults' => [
//                                'controller' => AdmissionController::class,
////                                'action' => 'justificatifs',
//                                'action' => "addInformationsJustificatifs"
//                            ],
//                        ],
//                    ],
                ],
            ],

        ],
    ],

    'controllers' => [
        'factories' => [
            AdmissionController::class => AdmissionControllerFactory::class,
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
