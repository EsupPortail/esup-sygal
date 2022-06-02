<?php

namespace Application;

use Application\Assertion\Rapport\RapportAssertion;
use Application\Controller\Factory\Rapport\RapportAvisControllerFactory;
use Application\Controller\Factory\Rapport\RapportCsiControllerFactory;
use Application\Controller\Factory\Rapport\RapportCsiRechercheControllerFactory;
use Application\Controller\Factory\Rapport\RapportMiparcoursControllerFactory;
use Application\Controller\Factory\Rapport\RapportMiparcoursRechercheControllerFactory;
use Application\Controller\Factory\Rapport\RapportValidationControllerFactory;
use Application\Controller\Rapport\RapportAvisController;
use Application\Controller\Rapport\RapportCsiController;
use Application\Controller\Rapport\RapportCsiRechercheController;
use Application\Controller\Rapport\RapportMiparcoursController;
use Application\Controller\Rapport\RapportMiparcoursRechercheController;
use Application\Controller\Rapport\RapportValidationController;
use Application\Form\Factory\RapportAvisFormFactory;
use Application\Form\Factory\RapportCsiFormFactory;
use Application\Form\Factory\RapportMiparcoursFormFactory;
use Application\Form\Rapport\RapportAvisForm;
use Application\Form\RapportCsiForm;
use Application\Form\RapportMiparcoursForm;
use Application\Provider\Privilege\RapportPrivileges;
use Application\Search\Controller\SearchControllerPluginFactory;
use Application\Service\Rapport\Avis\RapportAvisService;
use Application\Service\Rapport\Avis\RapportAvisServiceFactory;
use Application\Service\Rapport\RapportSearchService;
use Application\Service\Rapport\RapportSearchServiceFactory;
use Application\Service\Rapport\RapportService;
use Application\Service\Rapport\RapportServiceFactory;
use Application\Service\RapportValidation\RapportValidationService;
use Application\Service\RapportValidation\RapportValidationServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Rapport' => [],
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
                ////////////////////////////////////////// Rapports CSI //////////////////////////////////////////
                [
                    'controller' => RapportCsiController::class,
                    'action'     => [
                        'consulter',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_CSI_LISTER_TOUT,
                        RapportPrivileges::RAPPORT_CSI_LISTER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportCsiController::class,
                    'action'     => [
                        'telecharger',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_CSI_TELECHARGER_TOUT,
                        RapportPrivileges::RAPPORT_CSI_TELECHARGER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportCsiController::class,
                    'action'     => [
                        'ajouter',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_CSI_TELEVERSER_TOUT,
                        RapportPrivileges::RAPPORT_CSI_TELEVERSER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportCsiController::class,
                    'action'     => [
                        'supprimer',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_CSI_SUPPRIMER_TOUT,
                        RapportPrivileges::RAPPORT_CSI_SUPPRIMER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportCsiRechercheController::class,
                    'action'     => [
                        'index',
                        'filters',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_CSI_RECHERCHER_TOUT,
                        RapportPrivileges::RAPPORT_CSI_RECHERCHER_SIEN,
                    ],
                ],
                [
                    'controller' => RapportCsiRechercheController::class,
                    'action'     => [
                        'telecharger-zip',
                    ],
                    'privileges' => RapportPrivileges::RAPPORT_CSI_TELECHARGER_ZIP,
                ],

                //////////////////////////////////////// Rapports mi-parcours ////////////////////////////////////////
                [
                    'controller' => RapportMiparcoursController::class,
                    'action'     => [
                        'consulter',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_MIPARCOURS_LISTER_TOUT,
                        RapportPrivileges::RAPPORT_MIPARCOURS_LISTER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportMiparcoursController::class,
                    'action'     => [
                        'telecharger',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_MIPARCOURS_TELECHARGER_TOUT,
                        RapportPrivileges::RAPPORT_MIPARCOURS_TELECHARGER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportMiparcoursController::class,
                    'action'     => [
                        'ajouter',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_MIPARCOURS_TELEVERSER_TOUT,
                        RapportPrivileges::RAPPORT_MIPARCOURS_TELEVERSER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportMiparcoursController::class,
                    'action'     => [
                        'supprimer',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_MIPARCOURS_SUPPRIMER_TOUT,
                        RapportPrivileges::RAPPORT_MIPARCOURS_SUPPRIMER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportMiparcoursRechercheController::class,
                    'action'     => [
                        'index',
                        'filters',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_MIPARCOURS_RECHERCHER_TOUT,
                        RapportPrivileges::RAPPORT_MIPARCOURS_RECHERCHER_SIEN,
                    ],
                ],
                [
                    'controller' => RapportMiparcoursRechercheController::class,
                    'action'     => [
                        'telecharger-zip',
                    ],
                    'privileges' => RapportPrivileges::RAPPORT_MIPARCOURS_TELECHARGER_ZIP,
                ],

            ],
        ],
    ],
    'router' => [
        'routes' => [
            'rapport-csi' => [
                'type'          => 'Literal',
                'options'       => [
                    'route' => '/rapport-csi',
                    'defaults'      => [
                        'controller' => RapportCsiController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'recherche' => [
                        'type'          => 'Literal',
                        'options'       => [
                            'route' => '/recherche',
                            'defaults'      => [
                                'controller' => RapportCsiRechercheController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'index' => [
                                'type'          => 'Literal',
                                'options'       => [
                                    'route' => '/index',
                                    'defaults'      => [
                                        'action' => 'index',
                                    ],
                                ],
                            ],
                            'filters' => [
                                'type'          => 'Literal',
                                'options'       => [
                                    'route'       => '/filters',
                                    'defaults'    => [
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
                            'telecharger-zip'     => [
                                'type'     => 'Literal',
                                'options'  => [
                                    'route' => '/telecharger-zip',
                                    'defaults' => [
                                        'action' => 'telecharger-zip',
                                        /* @see RapportCsiRechercheController::telechargerZipAction() */
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'consulter'     => [
                        'type'     => 'Segment',
                        'options'  => [
                            'route' => '/consulter/:these',
                            'constraints'   => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'consulter',
                                /* @see \Application\Controller\Rapport\RapportCsiController::consulterAction() */
                            ],
                        ],
                    ],
                    'ajouter'  => [
                        'type'     => 'Segment',
                        'options'  => [
                            'route' => '/ajouter/:these',
                            'constraints'   => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'ajouter',
                                /* @see \Application\Controller\Rapport\RapportCsiController::ajouterAction() */
                            ],
                        ],
                    ],
                    'telecharger' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/telecharger/:rapport',
                            'constraints' => [
                                'rapport' => '\d+',
                            ],
                            'defaults'      => [
                                'action' => 'telecharger',
                                /* @see \Application\Controller\Rapport\RapportCsiController::telechargerAction() */
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type'        => 'Segment',
                        'options'     => [
                            'route' => '/supprimer/:rapport',
                            'constraints' => [
                                'rapport' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'supprimer',
                                /* @see RapportCsiController::supprimerAction() */
                            ],
                        ],
                    ],
                ],
            ],

            'rapport-miparcours' => [
                'type'          => 'Literal',
                'options'       => [
                    'route' => '/rapport-miparcours',
                    'defaults'      => [
                        'controller' => RapportMiparcoursController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'recherche' => [
                        'type'          => 'Literal',
                        'options'       => [
                            'route' => '/recherche',
                            'defaults'      => [
                                'controller' => RapportMiparcoursRechercheController::class,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes'  => [
                            'index' => [
                                'type'          => 'Literal',
                                'options'       => [
                                    'route' => '/index',
                                    'defaults'      => [
                                        'action' => 'index',
                                    ],
                                ],
                            ],
                            'filters' => [
                                'type'          => 'Literal',
                                'options'       => [
                                    'route'       => '/filters',
                                    'defaults'    => [
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
                            'telecharger-zip'     => [
                                'type'     => 'Literal',
                                'options'  => [
                                    'route' => '/telecharger-zip',
                                    'defaults' => [
                                        'action' => 'telecharger-zip',
                                        /* @see RapportMiparcoursRechercheController::telechargerZipAction() */
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'consulter'     => [
                        'type'     => 'Segment',
                        'options'  => [
                            'route' => '/consulter/:these',
                            'constraints'   => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'consulter',
                                /* @see RapportMiparcoursController::consulterAction() */
                            ],
                        ],
                    ],
                    'ajouter'  => [
                        'type'     => 'Segment',
                        'options'  => [
                            'route' => '/ajouter/:these',
                            'constraints'   => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'action' => 'ajouter',
                                /* @see RapportMiparcoursController::ajouterAction() */
                            ],
                        ],
                    ],
                    'telecharger' => [
                        'type'          => 'Segment',
                        'options'       => [
                            'route' => '/telecharger/:rapport',
                            'constraints' => [
                                'rapport' => '\d+',
                            ],
                            'defaults'      => [
                                'action' => 'telecharger',
                                /* @see RapportMiparcoursController::telechargerAction() */
                            ],
                        ],
                    ],
                    'supprimer' => [
                        'type'        => 'Segment',
                        'options'     => [
                            'route' => '/supprimer/:rapport',
                            'constraints' => [
                                'rapport' => '\d+',
                            ],
                            'defaults'    => [
                                'action' => 'supprimer',
                                /* @see RapportMiparcoursController::supprimerAction() */
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'admin' => [
                        'pages' => [
                            '-----------' => [
                                'label' => null,
                                'order' => 99,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                            'rapport-csi' => [
                                'label'    => "Rapports CSI",
                                'route'    => 'rapport-csi/recherche/index',
                                'order'    => 101,
                                'resource' => PrivilegeController::getResourceId(RapportCsiRechercheController::class, 'index'),
                                'privilege' => [
                                    RapportPrivileges::RAPPORT_CSI_RECHERCHER_TOUT,
                                    RapportPrivileges::RAPPORT_CSI_RECHERCHER_SIEN,
                                ],
                                'visible' => 'Assertion\\Rapport',
                            ],
                            'rapport-miparcours' => [
                                'label'    => "Rapports mi-parcours",
                                'route'    => 'rapport-miparcours/recherche/index',
                                'order'    => 102,
                                'resource' => PrivilegeController::getResourceId(RapportMiparcoursRechercheController::class, 'index'),
                                'privilege' => [
                                    RapportPrivileges::RAPPORT_MIPARCOURS_RECHERCHER_TOUT,
                                    RapportPrivileges::RAPPORT_MIPARCOURS_RECHERCHER_SIEN,
                                ],
                                'visible' => 'Assertion\\Rapport',
                            ],
                            '----------' => [
                                'label' => null,
                                'order' => 103,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            RapportService::class => RapportServiceFactory::class,
            RapportSearchService::class => RapportSearchServiceFactory::class,
            RapportValidationService::class => RapportValidationServiceFactory::class,
            RapportAvisService::class => RapportAvisServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            RapportCsiController::class => RapportCsiControllerFactory::class,
            RapportCsiRechercheController::class => RapportCsiRechercheControllerFactory::class,
            RapportMiparcoursController::class => RapportMiparcoursControllerFactory::class,
            RapportMiparcoursRechercheController::class => RapportMiparcoursRechercheControllerFactory::class,
            RapportValidationController::class => RapportValidationControllerFactory::class,
            RapportAvisController::class => RapportAvisControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'searchControllerPlugin' => SearchControllerPluginFactory::class,
        ],
    ],
    'form_elements'   => [
        'factories' => [
            RapportCsiForm::class => RapportCsiFormFactory::class,
            RapportMiparcoursForm::class => RapportMiparcoursFormFactory::class,
            RapportAvisForm::class => RapportAvisFormFactory::class,
        ],
    ],
];
