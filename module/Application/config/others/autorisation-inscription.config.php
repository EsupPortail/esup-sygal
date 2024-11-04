<?php

namespace Application;

use Application\Assertion\AutorisationInscription\AutorisationInscriptionAssertion;
use Application\Assertion\AutorisationInscription\AutorisationInscriptionAssertionFactory;
use Application\Controller\AutorisationInscriptionController;
use Application\Controller\Factory\AutorisationInscriptionControllerFactory;
use Application\Form\AutorisationInscriptionForm;
use Application\Form\Factory\AutorisationInscriptionFormFactory;
use Application\Provider\Privilege\AutorisationInscriptionPrivileges;
use Application\Service\AutorisationInscription\AutorisationInscriptionService;
use Application\Service\AutorisationInscription\AutorisationInscriptionServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'AutorisationInscription' => [],
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        'privileges' => [
                            AutorisationInscriptionPrivileges::AUTORISATION_INSCRIPTION_AJOUTER,
                        ],
                        'resources'  => ['AutorisationInscription'],
                        'assertion' => AutorisationInscriptionAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => AutorisationInscriptionController::class,
                    'action'     => [
                        'ajouter'
                    ],
                    'privileges' => [
                        AutorisationInscriptionPrivileges::AUTORISATION_INSCRIPTION_AJOUTER,
                    ],
                    'assertion' => AutorisationInscriptionAssertion::class,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'autoriser-inscription' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/autoriser-inscription',
                    'defaults' => [
                        'controller' => AutorisationInscriptionController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes' => [
                    'ajouter' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/ajouter/:rapport',
                            'constraints' => [
                                'rapport' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => AutorisationInscriptionController::class,
                                'action' => 'ajouter',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            AutorisationInscriptionService::class => AutorisationInscriptionServiceFactory::class,
            AutorisationInscriptionAssertion::class => AutorisationInscriptionAssertionFactory::class
        ],
    ],
    'controllers' => [
        'factories' => [
            AutorisationInscriptionController::class => AutorisationInscriptionControllerFactory::class,
        ],
    ],
    'form_elements'   => [
        'factories' => [
            AutorisationInscriptionForm::class => AutorisationInscriptionFormFactory::class
        ],
    ],
];
