<?php
namespace HDR;

use Application\Navigation\ApplicationNavigationFactory;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use HDR\Assertion\HDRAssertion;
use HDR\Assertion\HDRAssertionFactory;
use HDR\Controller\Factory\HDRControllerFactory;
use HDR\Controller\Factory\HDRRechercheControllerFactory;
use HDR\Controller\HDRController;
use HDR\Controller\HDRRechercheController;
use HDR\Provider\Privileges\HDRPrivileges;
use HDR\Service\HDRSearchService;
use HDR\Service\HDRSearchServiceFactory;
use HDR\Service\HDRService;
use HDR\Service\HDRServiceFactory;
use Laminas\Router\Http\Literal;
use Soutenance\Controller\AvisController;
use Soutenance\Controller\EngagementImpartialiteController;
use Soutenance\Controller\IndexController;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'HDR\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/HDR/Entity/Db/Mapping',
                ],
            ],
        ],
    ],
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'HDR' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                    [
                        //
                        // Privilèges concernant la ressource HDR SOUMIS À ASSERTION.
                        //
                        'privileges' => [
                            HDRPrivileges::HDR_CONSULTATION_TOUTES_HDRS,
                            HDRPrivileges::HDR_CONSULTATION_SES_HDRS,
                        ],
                        'resources' => ['HDR'],
                        'assertion' => HDRAssertion::class,
                    ],
                ],
            ],
        ],
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => HDRRechercheController::class,
                    'action' => [
                        'index',
                        'filters',
                        'notres',
                        'notresFilters',
                    ],
                    'privileges' => HDRPrivileges::HDR_CONSULTATION_SES_HDRS,
                ],
                [
                    'controller' => HDRController::class,
                    'action' => [
                        'index',
                        'hdr',
                        'detail-identite',
                    ],
                    'privileges' => HDRPrivileges::HDR_CONSULTATION_SES_HDRS,
                    'assertion' => HDRAssertion::class,
                ],
                [
                    'controller' => HDRController::class,
                    'action' => [
                        'demander-saisie-infos-soutenance',
                    ],
                    'privileges' => HDRPrivileges::HDR_MODIFICATION_SES_HDRS,
                    'assertion' => HDRAssertion::class,
                ],
                [
                    'controller' => HDRController::class,
                    'action' => [
                        'generer-export-csv',
                    ],
                    'privileges' => HDRPrivileges::HDR_EXPORT_CSV,
                ],
                [
                    'controller' => HDRController::class,
                    'action' => [
                        'filters',
                    ],
                    'roles' => 'guest',
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'hdr' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/hdr',
                    'defaults' => [
                        'controller' => HDRRechercheController::class,
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
                                'controller' => HDRRechercheController::class,
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
                            'route' => '/identite/:hdr',
                            'constraints' => [
                                'hdr' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => HDRController::class,
                                'action' => 'detail-identite',
                            ],
                        ],
                    ],
                    'demander-saisie-infos-soutenance' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/demander-saisie-infos-soutenance/:hdr',
                            'constraints' => [
                                'hdr' => '\d+',
                            ],
                            'defaults' => [
                                'controller' => HDRController::class,
                                'action' => 'demander-saisie-infos-soutenance',
                            ],
                        ],
                    ],
                    'export-csv' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/export-csv',
                            'defaults' => [
                                'controller' => HDRController::class,
                                'action' => 'generer-export-csv',
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
                     * Annuaire des HDR
                     */
                    // DEPTH = 1
//                    'annuaireHDR' => [
//                        'order' => 99,
//                        'label' => 'Annuaire des HDR',
//                        'route' => 'hdr',
//                        'params' => [],
//                        'query' => ['etatHDR' => 'E'],
//                        'resource' => PrivilegeController::getResourceId(HDRController::class, 'index'),
//                        'pages' => [
//                            // PAS de pages filles sinon le menu disparaît ! :-/
//                        ]
//                    ],

                    /**
                     * Navigation pour LA HDR "sélectionnée".
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::HDR_SELECTIONNEE_PAGE_ID => [
                        'label' => 'HDR sélectionnée',
                        'route' => 'hdr/identite',
                        'order' => -300,
                        'withtarget' => true,
                        'paramsInject' => [
                            'hdr',
                        ],
                        'resource' => PrivilegeController::getResourceId(HDRController::class, 'index'),
                        'pages' => $hdrPages = [
                            // DEPTH = 3
                            'identite' => [
                                'label' => 'Fiche',
                                'order' => 10,
                                'route' => 'hdr/identite',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'hdr',
                                ],
                                'icon' => 'fas fa-info-circle',
                                'resource' => PrivilegeController::getResourceId(HDRController::class, 'detail-identite'),
                                'etape' => null,
                                'visible' => HDRAssertion::class,
                            ],
                            'divider-1' => [
                                'label' => null,
                                'order' => 15,
                                'uri' => '',
                                'class' => 'divider',
                                'separator' => true,
                            ],
//                            'page-rapporteur_hdr' => [
//                                'order' => 60,
//                                'label' => 'Dépôt du rapport',
//                                'route' => 'soutenance_hdr/index-rapporteur',
//                                'withtarget' => true,
//                                'paramsInject' => [
//                                    'hdr',
//                                ],
//                                'icon' => 'fas fa-clipboard',
//                                'resource' => PrivilegeController::getResourceId(IndexController::class, 'index-rapporteur'),
//                                'child_routes' => [
//                                    'engagement' => [
//                                        'label' => 'Engagement d\'impartialité',
//                                        'route' => 'soutenance_hdr/engagement-impartialite',
//                                        'order' => 300,
//                                        'resource' => PrivilegeController::getResourceId(EngagementImpartialiteController::class, 'engagement-impartialite'),
//                                        'withtarget' => true,
//                                        'paramsInject' => [
//                                            'Acteur',
//                                            'hdr'
//                                        ],
//                                    ],
//                                    'avis' => [
//                                        'label' => 'Avis de soutenance',
//                                        'route' => 'soutenance_hdr/avis-soutenance',
//                                        'order' => 400,
//                                        'resource' => PrivilegeController::getResourceId(AvisController::class, 'index'),
//                                        'withtarget' => true,
//                                        'paramsInject' => [
//                                            'Acteur',
//                                            'hdr'
//                                        ],
//                                    ],
//                                ]
//                            ],
                        ],
                    ],

                    /**
                     * Page pour Candidat.
                     * Cette page sera dupliquée en 'mon-HDR-1', 'mon-HDR-2', etc. automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MA_HDR_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Mon HDR',
                        'route' => 'hdr/identite',
                        'params' => [
                            'hdr' => 0,
                        ],
                        'resource' => PrivilegeController::getResourceId(HDRController::class, 'index'),
                        'pages' => $hdrPages,
                    ],

                    /**
                     * Page pour Garant.
                     * Cette page aura des pages filles 'HDR-1', 'HDR-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MES_HDR_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Mes HDR',
                        'uri' => '',
                        'resource' => PrivilegeController::getResourceId(HDRController::class, 'index'),
                        'pages' => [
                            // DEPTH = 2
                            // Déclinée en 'HDR-1', 'HDR-2', etc.
                            'HDR' => [
                                'label' => '(Candidat)',
                                'route' => 'hdr/identite',
                                'params' => [
                                    'hdr' => 0,
                                ],
                                'resource' => PrivilegeController::getResourceId(HDRController::class, 'index'),
                                'pages' => $hdrPages,
                            ]
                        ]
                    ],

                    /**
                     * Cette page aura une page fille 'HDR-1', 'HDR-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::NOS_HDR_PAGE_ID => [
                        'order' => -200,
                        'label' => 'Nos HDR',
                        'route' => 'hdr/recherche/notres',
                        'resource' => PrivilegeController::getResourceId(HDRController::class, 'index'),
                        'pages' => [
                            // DEPTH = 2
                            'HDR' => [
                                'label' => '(HDR Structure)',
                                'route' => 'hdr/recherche/notres',
                                'params' => [],
                                'query' => ['etatHDR' => 'E'], // injection automatique du filtre "structure"
                                'resource' => PrivilegeController::getResourceId(HDRController::class, 'index'),
                            ],
                        ],
                    ],

                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            HDRController::class => HDRControllerFactory::class,
            HDRRechercheController::class => HDRRechercheControllerFactory::class
        ],
    ],
    'service_manager' => [
        'factories' => [
            HDRService::class => HDRServiceFactory::class,
            HDRSearchService::class => HDRSearchServiceFactory::class,
            HDRAssertion::class => HDRAssertionFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];