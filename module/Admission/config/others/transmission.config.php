<?php

namespace Admission;

use Admission\Assertion\AdmissionAssertion;
use Admission\Controller\Transmission\TransmissionController;
use Admission\Controller\Transmission\TransmissionControllerFactory;
use Admission\Form\Transmission\TransmissionForm;
use Admission\Form\Transmission\TransmissionFormFactory;
use Admission\Provider\Privilege\AdmissionPrivileges;
use Admission\Service\Transmission\TransmissionService;
use Admission\Service\Transmission\TransmissionServiceFactory;
use Laminas\Router\Http\Segment;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return array(
    'bjyauthorize' => [
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            AdmissionPrivileges::ADMISSION_AJOUTER_DONNEES_EXPORT,
                        ],
                        'resources'  => ['Admission'],
                        'assertion' => AdmissionAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => TransmissionController::class,
                    'action' => [
                        'ajouter-transmission',
                    ],
                    'privileges' => [
                        AdmissionPrivileges::ADMISSION_AJOUTER_DONNEES_EXPORT,
                    ],
                    'assertion' => AdmissionAssertion::class,
                ],
            ]
        ],
    ],
    'router' => [
        'routes' => [
            'admission' => [
                'child_routes' => [
                    'ajouter-transmission' => [
                        'type'  => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/transmission/ajouter/:admission',
                            'defaults' => [
                                'controller' => TransmissionController::class,
                                'action'     => 'ajouter-transmission',
                                /* @see TransmissionController::ajouterTransmissionAction() */
                            ],
                        ],
                    ],
                ]
            ]
        ]
    ],

    'controllers' => [
        'factories' => [
            TransmissionController::class => TransmissionControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            TransmissionForm::class => TransmissionFormFactory::class
        ],
    ],

    'service_manager' => [
        'factories' => [
            TransmissionService::class => TransmissionServiceFactory::class,
        ],
    ],
);
