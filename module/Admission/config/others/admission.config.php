<?php

namespace Admission;


use Admission\Controller\AdmissionController;
use Admission\Service\Notification\NotificationFactory;
use Admission\Service\Notification\NotificationFactoryFactory;
use Laminas\Router\Http\Literal;
use UnicaenAuth\Guard\PrivilegeController;

return array(
    'bjyauthorize' => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => AdmissionController::class,
                    'action' => [
                        'envoyerMail'
                    ],
                ]
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'admission' => [
                'child_routes' => [
                    'envoyer-mail' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/envoyer-mail',
                            'defaults' => [
                                /** @see AdmissionController::envoyerMailAction() */
                                'controller' => AdmissionController::class,
                                'action' => 'envoyerMail'
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'controllers' => [
        'factories' => [
//            AdmissionController::class => AdmissionControllerFactory::class,
        ],
    ],

    'form_manager' => [
        'factories' => [
//            IndividuFieldset::class => InvokableFactory::class,
//            InscriptionFieldset::class => InvokableFactory::class,
//            FinancementFieldset::class => InvokableFactory::class,
//            ValidationFieldset::class => InvokableFactory::class
        ],
    ],

    'service_manager' => [
        'factories' => [
            NotificationFactory::class => NotificationFactoryFactory::class,
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
