<?php

namespace Application;

use Application\Assertion\Rapport\RapportAssertion;
use Application\Controller\Factory\Rapport\RapportActiviteControllerFactory;
use Application\Controller\Factory\Rapport\RapportActiviteRechercheControllerFactory;
use Application\Controller\Factory\Rapport\RapportAvisControllerFactory;
use Application\Controller\Factory\Rapport\RapportCsiControllerFactory;
use Application\Controller\Factory\Rapport\RapportCsiRechercheControllerFactory;
use Application\Controller\Factory\Rapport\RapportMiparcoursControllerFactory;
use Application\Controller\Factory\Rapport\RapportMiparcoursRechercheControllerFactory;
use Application\Controller\Factory\Rapport\RapportValidationControllerFactory;
use Application\Controller\Rapport\RapportActiviteController;
use Application\Controller\Rapport\RapportActiviteRechercheController;
use Application\Controller\Rapport\RapportAvisController;
use Application\Controller\Rapport\RapportCsiController;
use Application\Controller\Rapport\RapportCsiRechercheController;
use Application\Controller\Rapport\RapportMiparcoursController;
use Application\Controller\Rapport\RapportMiparcoursRechercheController;
use Application\Controller\Rapport\RapportValidationController;
use Application\Form\Factory\RapportActiviteFormFactory;
use Application\Form\Factory\RapportAvisFormFactory;
use Application\Form\Factory\RapportCsiFormFactory;
use Application\Form\Factory\RapportMiparcoursFormFactory;
use Application\Form\Rapport\RapportAvisForm;
use Application\Form\RapportActiviteForm;
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
                            RapportPrivileges::RAPPORT_ACTIVITE_LISTER_TOUT,
                            RapportPrivileges::RAPPORT_ACTIVITE_LISTER_SIEN,
                            RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT,
                            RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN,
                            RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN,
                            RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT,
                            RapportPrivileges::RAPPORT_ACTIVITE_RECHERCHER_SIEN,
                            RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_TOUT,
                            RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN,
                            RapportPrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT,
                            RapportPrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN,
                            RapportPrivileges::RAPPORT_ACTIVITE_DEVALIDER_TOUT,
                            RapportPrivileges::RAPPORT_ACTIVITE_DEVALIDER_SIEN,
                            RapportPrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT,
                            RapportPrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN,
                            RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT,
                            RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN,

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
                ////////////////////////////////////////// Rapports activité //////////////////////////////////////////
                [
                    'controller' => RapportActiviteController::class,
                    'action'     => [
                        'consulter',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_LISTER_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_LISTER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportActiviteController::class,
                    'action'     => [
                        'telecharger',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportActiviteController::class,
                    'action'     => [
                        'ajouter',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_TELEVERSER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportActiviteController::class,
                    'action'     => [
                        'supprimer',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportActiviteRechercheController::class,
                    'action'     => [
                        'index',
                        'filters',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_RECHERCHER_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_RECHERCHER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportActiviteRechercheController::class,
                    'action'     => [
                        'telecharger-zip',
                    ],
                    'privileges' => RapportPrivileges::RAPPORT_ACTIVITE_TELECHARGER_ZIP,
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportValidationController::class,
                    'action'     => [
                        'valider',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_VALIDER_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_VALIDER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportValidationController::class,
                    'action'     => [
                        'devalider',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_DEVALIDER_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_DEVALIDER_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportAvisController::class,
                    'action'     => [
                        'ajouter',
                        'modifier',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_AJOUTER_AVIS_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],
                [
                    'controller' => RapportAvisController::class,
                    'action'     => [
                        'supprimer',
                    ],
                    'privileges' => [
                        RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_TOUT,
                        RapportPrivileges::RAPPORT_ACTIVITE_SUPPRIMER_AVIS_SIEN,
                    ],
                    'assertion' => 'Assertion\\Rapport',
                ],

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
            'rapport-activite' => [
                'type'          => 'Literal',
                'options'       => [
                    'route' => '/rapport-activite',
                    'defaults'      => [
                        'controller' => RapportActiviteController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'recherche' => [
                        'type'          => 'Literal',
                        'options'       => [
                            'route' => '/recherche',
                            'defaults'      => [
                                'controller' => RapportActiviteRechercheController::class,
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
                                        /* @see RapportActiviteRechercheController::telechargerZipAction() */
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
                                /* @see \Application\Controller\Rapport\RapportActiviteController::consulterAction() */
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
                                /* @see \Application\Controller\Rapport\RapportActiviteController::ajouterAction() */
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
                                /* @see \Application\Controller\Rapport\RapportActiviteController::telechargerAction() */
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
                                /* @see RapportActiviteController::supprimerAction() */
                            ],
                        ],
                    ],
                    'valider' => [
                        'type'        => 'Segment',
                        'options'     => [
                            'route' => '/valider/:rapport/type/:typeValidation',
                            'constraints' => [
                                'rapport' => '\d+',
                                'typeValidation' => '\d+',
                            ],
                            'defaults'    => [
                                'controller' => RapportValidationController::class,
                                'action' => 'valider',
                                /* @see RapportValidationController::validerAction() */
                            ],
                        ],
                    ],
                    'devalider' => [
                        'type'        => 'Segment',
                        'options'     => [
                            'route' => '/devalider/:rapportValidation',
                            'constraints' => [
                                'rapportValidation' => '\d+',
                            ],
                            'defaults'    => [
                                'controller' => RapportValidationController::class,
                                'action' => 'devalider',
                                /* @see RapportValidationController::devaliderAction() */
                            ],
                        ],
                    ],
                    'avis' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/avis',
                            'defaults' => [
                                'controller' => RapportAvisController::class,
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
                                        'action' => 'ajouter',
                                        /* @see RapportAvisController::ajouterAction() */
                                    ],
                                ],
                            ],
                            'modifier' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/modifier/:rapportAvis',
                                    'constraints' => [
                                        'rapportAvis' => '\d+',
                                    ],
                                    'defaults' => [
                                        'action' => 'modifier',
                                        /* @see RapportAvisController::modifierAction() */
                                    ],
                                ],
                            ],
                            'supprimer' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/supprimer/:rapportAvis',
                                    'constraints' => [
                                        'rapportAvis' => '\d+',
                                    ],
                                    'defaults' => [
                                        'action' => 'supprimer',
                                        /* @see RapportAvisController::supprimerAction() */
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

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
                            'rapport-activite' => [
                                'label'    => "Rapports d'activité",
                                'route'    => 'rapport-activite/recherche/index',
                                'order'    => 100,
                                'resource' => PrivilegeController::getResourceId(RapportActiviteRechercheController::class, 'index'),
                                'privilege' => [
                                    RapportPrivileges::RAPPORT_ACTIVITE_RECHERCHER_TOUT,
                                    RapportPrivileges::RAPPORT_ACTIVITE_RECHERCHER_SIEN,
                                ],
                                'visible' => 'Assertion\\Rapport',
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
            RapportActiviteController::class => RapportActiviteControllerFactory::class,
            RapportActiviteRechercheController::class => RapportActiviteRechercheControllerFactory::class,
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
            RapportActiviteForm::class => RapportActiviteFormFactory::class,
            RapportCsiForm::class => RapportCsiFormFactory::class,
            RapportMiparcoursForm::class => RapportMiparcoursFormFactory::class,
            RapportAvisForm::class => RapportAvisFormFactory::class,
        ],
    ],
];
