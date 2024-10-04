<?php

namespace Application;

use Application\Controller\AutorisationInscriptionController;
use Application\Controller\Factory\AutorisationInscriptionControllerFactory;
use Application\Form\AutorisationInscriptionForm;
use Application\Form\Factory\AutorisationInscriptionFormFactory;
use Application\Provider\Privilege\RapportPrivileges;
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
                            RapportPrivileges::RAPPORT_CSI_LISTER_TOUT,
                            RapportPrivileges::RAPPORT_CSI_LISTER_SIEN,
                            RapportPrivileges::RAPPORT_CSI_TELEVERSER_TOUT,
                            RapportPrivileges::RAPPORT_CSI_TELEVERSER_SIEN,
                            RapportPrivileges::RAPPORT_CSI_SUPPRIMER_SIEN,
                            RapportPrivileges::RAPPORT_CSI_SUPPRIMER_TOUT,
                            RapportPrivileges::RAPPORT_CSI_RECHERCHER_SIEN,
                            RapportPrivileges::RAPPORT_CSI_TELECHARGER_TOUT,
                            RapportPrivileges::RAPPORT_CSI_TELECHARGER_SIEN,

                            RapportPrivileges::RAPPORT_MIPARCOURS_LISTER_TOUT,
                            RapportPrivileges::RAPPORT_MIPARCOURS_LISTER_SIEN,
                            RapportPrivileges::RAPPORT_MIPARCOURS_TELEVERSER_TOUT,
                            RapportPrivileges::RAPPORT_MIPARCOURS_TELEVERSER_SIEN,
                            RapportPrivileges::RAPPORT_MIPARCOURS_SUPPRIMER_SIEN,
                            RapportPrivileges::RAPPORT_MIPARCOURS_SUPPRIMER_TOUT,
                            RapportPrivileges::RAPPORT_MIPARCOURS_RECHERCHER_SIEN,
                            RapportPrivileges::RAPPORT_MIPARCOURS_TELECHARGER_TOUT,
                            RapportPrivileges::RAPPORT_MIPARCOURS_TELECHARGER_SIEN,
                        ],
                        'resources'  => ['Rapport'],
                        'assertion' => 'Assertion\\Rapport', /** @see RapportAssertion */
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => AutorisationInscriptionController::class,
                    'action'     => [
                        'autoriser-inscription', // à modifier ensuite,
                        'ajouter'
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_CSI_LISTER_TOUT,
                        RapportPrivileges::RAPPORT_CSI_LISTER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
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
            AutorisationInscriptionService::class => AutorisationInscriptionServiceFactory::class
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
