<?php

namespace Admission;


use Admission\Controller\AdmissionControllerFactory;
use Admission\Fieldset\Etudiant\EtudiantFieldset;
use Admission\Fieldset\Financement\FinancementFieldset;
use Admission\Fieldset\Inscription\InscriptionFieldset;
use Admission\Fieldset\Justificatifs\ValidationFieldset;
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
                        'ajouter',
                        'etudiant',
                        'inscription',
                        'financement',
                        'validation',
                        'confirmer',
                        'annuler',
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
                    'infos-etudiant' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route' => '/informations-etudiant',
                            'defaults' => [
                                'controller' => AdmissionController::class,
                                'action' => "addInformationsEtudiant"
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
//                                'action' => 'inscription',
                                'action' => "addInformationsInscription"
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
//                                'action' => 'financement',
                                'action' => "addInformationsFinancement"
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
//                                'action' => 'justificatifs',
                                'action' => "addInformationsJustificatifs"
                            ],
                        ],
                    ],
                ],
            ],

        ],
    ],

    'controllers' => [
        'factories' => [
            AdmissionController::class => AdmissionControllerFactory::class,
        ],
    ],

    'form_manager' => [
        'factories' => [
            EtudiantFieldset::class => InvokableFactory::class,
            InscriptionFieldset::class => InvokableFactory::class,
            FinancementFieldset::class => InvokableFactory::class,
            ValidationFieldset::class => InvokableFactory::class
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
        'head_scripts' => [
            '080_uploader' => "/js/admission.js",
        ],
    ],
);
