<?php

namespace These;

use Application\Controller\Rapport\RapportCsiController;
use Application\Controller\Rapport\RapportMiparcoursController;
use Application\Navigation\ApplicationNavigationFactory;
use Application\Service\Financement\FinancementService;
use Application\Service\Financement\FinancementServiceFactory;
use Application\Service\ServiceAwareInitializer;
use Soutenance\Controller\IndexController;
use These\Assertion\These\TheseAssertion;
use These\Assertion\These\TheseAssertionFactory;
use These\Assertion\These\TheseEntityAssertion;
use These\Assertion\These\TheseEntityAssertionFactory;
use These\Controller\Factory\TheseControllerFactory;
use These\Controller\Factory\TheseRechercheControllerFactory;
use These\Controller\Plugin\Url\UrlThesePluginFactory;
use These\Controller\TheseController;
use These\Controller\TheseRechercheController;
use These\Hydrator\ActeurHydrator;
use These\Hydrator\ActeurHydratorFactory;
use These\Provider\Privilege\ThesePrivileges;
use These\Service\Exporter\CoEncadrements\CoEncadrementsExporter;
use These\Service\Exporter\CoEncadrements\CoEncadrementsExporterFactory;
use These\Service\These\Factory\TheseSearchServiceFactory;
use These\Service\These\Factory\TheseServiceFactory;
use These\Service\These\TheseSearchService;
use These\Service\These\TheseService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivService;
use These\Service\TheseAnneeUniv\TheseAnneeUnivServiceFactory;
use These\Service\Url\UrlTheseService;
use These\Service\Url\UrlTheseServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'These' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    //
                    // Privilèges concernant la ressource These *NON* SOUMIS À ASSERTION.
                    //
                    [
                        'privileges' => [
                            ThesePrivileges::THESE_REFRESH,
                        ],
                        'resources' => ['These'],
                    ],
                    [
                        //
                        // Privilèges concernant la ressource These SOUMIS À ASSERTION.
                        //
                        'privileges' => [
                            ThesePrivileges::THESE_CONSULTATION_TOUTES_THESES,
                            ThesePrivileges::THESE_CONSULTATION_SES_THESES,
//                            ThesePrivileges::THESE_MODIFICATION_TOUTES_THESES,
//                            ThesePrivileges::THESE_MODIFICATION_SES_THESES,
                        ],
                        'resources' => ['These'],
                        'assertion' => TheseAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => TheseRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                        'notres',
                        'notresFilters',
                    ],
                    'roles' => 'user',
                ],
                [
                    'controller' => TheseController::class,
                    'action' => [
                        'index',
                        'these',
                        'detail-identite',
                    ],
                    'roles' => 'user',
                ],
                [
                    'controller' => TheseController::class,
                    'action' => [
                        'filters',
                    ],
                    'roles' => 'guest',
                ],
                [
                    'controller' => TheseController::class,
                    'action' => [
                        'refresh-these',
                    ],
                    'privileges' => ThesePrivileges::THESE_REFRESH,
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'these' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/these',
                    'defaults' => [
                        'controller' => TheseRechercheController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'recherche' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/recherche',
                            'defaults' => [
                                'controller' => TheseRechercheController::class,
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'filters' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'action' => 'filters',
                                    ],
                                ],
                            ],
                            'notres' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/notres',
                                    'defaults' => [
                                        'action' => 'notres',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'filters' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/filters',
                                            'defaults' => [
                                                'action' => 'notresFilters',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'identite' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/identite/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => TheseController::class,
                                'action' => 'detail-identite',
                            ],
                        ],
                    ],
                    'refresh-these' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/refresh/:these',
                            'constraints' => [
                                'these' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => TheseController::class,
                                'action' => 'refresh-these',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation' => [
        'default' => [
            // DEPTH = 0
            'home' => [
                'pages' => [

                    /**
                     * Annuaire des thèses
                     */
                    // DEPTH = 1
                    'annuaire' => [
                        'order' => -50,
                        'label' => 'Annuaire des thèses',
                        'route' => 'these',
                        'params' => [],
                        'query' => ['etatThese' => 'E'],
                        'resource' => PrivilegeController::getResourceId(TheseController::class, 'index'),
                        'pages' => [
                            // PAS de pages filles sinon le menu disparaît ! :-/
                        ]
                    ],

                    /**
                     * Navigation pour LA thèse "sélectionnée".
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::THESE_SELECTIONNEE_PAGE_ID => [
                        'label' => 'Thèse sélectionnée',
                        'route' => 'these/identite',
                        'withtarget' => true,
                        'paramsInject' => [
                            'these',
                        ],
                        'resource' => PrivilegeController::getResourceId(TheseController::class, 'index'),
                        'pages' => $thesePages = [
                            // DEPTH = 3
                            'identite' => [
                                'label' => 'Fiche',
                                'order' => 10,
                                'route' => 'these/identite',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'icon' => 'fas fa-info-circle',
                                'resource' => PrivilegeController::getResourceId(TheseController::class, 'detail-identite'),
                                'etape' => null,
                                'visible' => TheseAssertion::class,
                            ],
                            'divider-1' => [
                                'label' => null,
                                'order' => 15,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                            //---------------------------------------------------
                            'rapport-csi' => [
                                'label' => 'Rapports CSI',
                                'order' => 30,
                                'route' => 'rapport-csi/consulter',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'resource' => PrivilegeController::getResourceId(RapportCsiController::class, 'consulter'),
                                'visible' => 'Assertion\\Rapport',
                            ],
                            'rapport-miparcours' => [
                                'label' => 'Rapports mi-parcours',
                                'order' => 30,
                                'route' => 'rapport-miparcours/consulter',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'resource' => PrivilegeController::getResourceId(RapportMiparcoursController::class, 'consulter'),
                                'visible' => 'Assertion\\Rapport',
                            ],
                            'divider-2' => [
                                'label' => null,
                                'order' => 45,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
                            'page-rapporteur' => [
                                'order' => 60,
                                'label' => 'Dépôt du rapport',
                                'route' => 'soutenance/index-rapporteur',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'icon' => 'fas fa-clipboard',
                                'resource' => PrivilegeController::getResourceId(IndexController::class, 'index-rapporteur'),
                            ],
                        ],
                    ],

                    /**
                     * Page pour Doctorant.
                     * Cette page sera dupliquée en 'ma-these-1', 'ma-these-2', etc. automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MA_THESE_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Ma thèse',
                        'route' => 'these/identite',
                        'params' => [
                            'these' => 0,
                        ],
                        'resource' => PrivilegeController::getResourceId(TheseController::class, 'index'),
                        'pages' => $thesePages,
                    ],

                    /**
                     * Page pour Dir, Codir.
                     * Cette page aura des pages filles 'these-1', 'these-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MES_THESES_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Mes thèses',
                        'uri' => '',
                        'resource' => PrivilegeController::getResourceId(TheseController::class, 'index'),
                        'pages' => [
                            // DEPTH = 2
                            // Déclinée en 'these-1', 'these-2', etc.
                            'THESE' => [
                                'label' => '(Doctorant)',
                                'route' => 'these/identite',
                                'params' => [
                                    'these' => 0,
                                ],
                                'resource' => PrivilegeController::getResourceId(TheseController::class, 'index'),
                                'pages' => $thesePages,
                            ]
                        ]
                    ],

                    /**
                     * Cette page aura une page fille 'these-1', 'these-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::NOS_THESES_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Nos thèses',
                        'route' => 'these/recherche/notres',
                        'resource' => PrivilegeController::getResourceId(TheseController::class, 'index'),
                        'pages' => [
                            // DEPTH = 2
                            'THESES' => [
                                'label' => '(Thèses Structure)',
                                'route' => 'these/recherche/notres',
                                'params' => [],
                                'query' => ['etatThese' => 'E'], // injection automatique du filtre "structure"
                                'resource' => PrivilegeController::getResourceId(TheseController::class, 'index'),
                            ],
                        ],
                    ],

                ],
            ],
        ],
    ],
    'form_elements' => [
        'initializers' => [
            ServiceAwareInitializer::class,
        ]
    ],
    'hydrators' => array(
        'factories' => array(
            ActeurHydrator::class => ActeurHydratorFactory::class,
        )
    ),
    'service_manager' => [
        'factories' => [
            UrlTheseService::class => UrlTheseServiceFactory::class,

            'TheseService' => TheseServiceFactory::class,
            TheseSearchService::class => TheseSearchServiceFactory::class,
            FinancementService::class => FinancementServiceFactory::class,
            TheseAnneeUnivService::class => TheseAnneeUnivServiceFactory::class,

            TheseAssertion::class => TheseAssertionFactory::class,
            TheseEntityAssertion::class => TheseEntityAssertionFactory::class,

            CoEncadrementsExporter::class => CoEncadrementsExporterFactory::class
        ],
        'aliases' => [
            TheseService::class => 'TheseService',
        ]
    ],
    'controllers' => [
        'factories' => [
            TheseController::class => TheseControllerFactory::class,
            TheseRechercheController::class => TheseRechercheControllerFactory::class,
        ],
        'aliases' => [
            'TheseController' => TheseController::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'urlThese' => UrlThesePluginFactory::class,
        ],
    ],
];
