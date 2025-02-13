<?php

namespace Soutenance;

use Application\Navigation\ApplicationNavigationFactory;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Soutenance\Controller\AbstractSoutenanceControllerFactory;
use Soutenance\Controller\AvisController;
use Soutenance\Controller\EngagementImpartialiteController;
use Soutenance\Controller\IndexController;
use Soutenance\Provider\Privilege\IndexPrivileges;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Membre\MembreServiceFactory;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryFactory;
use Soutenance\Service\Url\UrlService;
use Soutenance\Service\Url\UrlServiceFactory;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRService;
use Soutenance\Service\Validation\ValidationHDR\ValidationHDRServiceFactory;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseService;
use Soutenance\Service\Validation\ValidationThese\ValidationTheseServiceFactory;
use UnicaenPrivilege\Guard\PrivilegeController;
use UnicaenPrivilege\Provider\Rule\PrivilegeRuleProvider;

return [
    'bjyauthorize' => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Acteur' => [],
            ],
        ],
        'rule_providers' => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                ],
            ],
        ],
    ],

    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'Soutenance\Entity' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Soutenance/Entity/Mapping',
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [

                    ////////////////////////////////// Thèse ////////////////////////////////////////

                    /**
                     * Navigation pour LA thèse courante.
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::THESE_SELECTIONNEE_PAGE_ID => [
                        'pages' => [
                            // DEPTH = 2
                            'soutenance_these' => [
                                'order' => 55,
                                'label' => 'Soutenance',
                                'route' => 'soutenance_these/proposition',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'icon' => 'fas fa-chalkboard-teacher',
                                'resource' => PresoutenancePrivileges::getResourceId(PropositionPrivileges::PROPOSITION_VISUALISER),
                                'pages' => $soutenancePages = [
                                    // DEPTH = 3
                                    'proposition' => [
                                        'label' => 'Proposition de soutenance',
                                        'route' => 'soutenance_these/proposition',
                                        'order' => 100,
                                        'resource' => PresoutenancePrivileges::getResourceId(PropositionPrivileges::PROPOSITION_VISUALISER),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                    ],
                                    'presoutenance' => [
                                        'label' => 'Préparation de la soutenance',
                                        'route' => 'soutenance_these/presoutenance',
                                        'order' => 200,
                                        'resource' => PresoutenancePrivileges::getResourceId(PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                    ],
                                    'horodatages' => [
                                        'label' => 'Horodatages des événements',
                                        'route' => 'soutenance_these/proposition/horodatages',
                                        'order' => 1000,
                                        'resource' => PresoutenancePrivileges::getResourceId(PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                    ],
//                                    'retard' => [
//                                        'label' => 'Notifier attente de rapport',
//                                        'route' => 'soutenance_these/notifier-retard-rapport-presoutenance',
//                                        'order' => 500,
//                                        'resource' => PresoutenancePrivileges::getResourceId(PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION),
//                                    ],
                                ],
                            ],
                            'page-rapporteur_these' => [
                                'order' => 60,
                                'label' => 'Dépôt du rapport',
                                'route' => 'soutenance_these/index-rapporteur',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'icon' => 'fas fa-clipboard',
                                'resource' => PrivilegeController::getResourceId(IndexController::class, 'index-rapporteur'),
                                'child_routes' => [
                                    'engagement' => [
                                        'label' => 'Engagement d\'impartialité',
                                        'route' => 'soutenance_these/engagement-impartialite',
                                        'order' => 300,
                                        'resource' => PrivilegeController::getResourceId(EngagementImpartialiteController::class, 'engagement-impartialite'),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'Acteur',
                                            'these'
                                        ],
                                    ],
                                    'avis' => [
                                        'label' => 'Avis de soutenance',
                                        'route' => 'soutenance_these/avis-soutenance',
                                        'order' => 400,
                                        'resource' => PrivilegeController::getResourceId(AvisController::class, 'index'),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'Acteur',
                                            'these'
                                        ],
                                    ],
                                ]
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
                        'pages' => [
                            // DEPTH = 2
                            'soutenance_these' => [
                                'order' => 55,
                                'label' => 'Soutenance',
                                'route' => 'soutenance_these/proposition',
                                'params' => [
                                    'these' => 0
                                ],
                                'icon' => 'fas fa-chalkboard-teacher',
                                'resource' => PresoutenancePrivileges::getResourceId(PropositionPrivileges::PROPOSITION_VISUALISER),
                                'pages' => $soutenancePages,
                            ],
                        ],
                    ],

                    /**
                     * Page pour Dir, Codir.
                     * Cette page aura des pages filles 'these-1', 'these-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MES_THESES_PAGE_ID => [
                        'pages' => [
                            // Déclinée en 'these-1', 'these-2', etc.
                            // DEPTH = 2
                            'THESE' => [
                                'pages' => [
                                    // DEPTH = 3
                                    'soutenance_these' => [
                                        'order' => 50,
                                        'label' => 'Soutenance',
                                        'route' => 'soutenance_these/proposition',
                                        'params' => [
                                            'these' => 0
                                        ],
                                        'icon' => 'fas fa-chalkboard-teacher',
                                        'resource' => PresoutenancePrivileges::getResourceId(PropositionPrivileges::PROPOSITION_VISUALISER),
                                        'pages' => $soutenancePages,
                                    ],
                                ],
                            ],
                        ],
                    ],


                    ////////////////////////////////// HDR ////////////////////////////////////////

                    /**
                     * Navigation pour LA hdr courante.
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::HDR_SELECTIONNEE_PAGE_ID => [
                        'pages' => [
                            // DEPTH = 2
                            'soutenance_hdr' => [
                                'order' => 55,
                                'label' => 'Soutenance',
                                'route' => 'soutenance_hdr/proposition',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'hdr',
                                ],
                                'icon' => 'fas fa-chalkboard-teacher',
                                'resource' => PresoutenancePrivileges::getResourceId(PropositionPrivileges::PROPOSITION_VISUALISER),
                                'pages' => $soutenancePages = [
                                    // DEPTH = 3
                                    'proposition' => [
                                        'label' => 'Proposition de soutenance',
                                        'route' => 'soutenance_hdr/proposition',
                                        'order' => 100,
                                        'resource' => PresoutenancePrivileges::getResourceId(PropositionPrivileges::PROPOSITION_VISUALISER),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'hdr',
                                        ],
                                    ],
                                    'presoutenance' => [
                                        'label' => 'Préparation de la soutenance',
                                        'route' => 'soutenance_hdr/presoutenance',
                                        'order' => 200,
                                        'resource' => PresoutenancePrivileges::getResourceId(PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'hdr',
                                        ],
                                    ],
                                    'horodatages' => [
                                        'label' => 'Horodatages des événements',
                                        'route' => 'soutenance_hdr/proposition/horodatages',
                                        'order' => 1000,
                                        'resource' => PresoutenancePrivileges::getResourceId(PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'hdr',
                                        ],
                                    ],
//                                    'retard' => [
//                                        'label' => 'Notifier attente de rapport',
//                                        'route' => 'soutenance_hdr/notifier-retard-rapport-presoutenance',
//                                        'order' => 500,
//                                        'resource' => PresoutenancePrivileges::getResourceId(PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION),
//                                    ],
                                ],
                            ],
                            'page-rapporteur' => [
                                'order' => 60,
                                'label' => 'Dépôt du rapport',
                                'route' => 'soutenance_hdr/index-rapporteur',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'hdr',
                                ],
                                'icon' => 'fas fa-clipboard',
                                'resource' => PrivilegeController::getResourceId(IndexController::class, 'index-rapporteur'),
                                'child_routes' => [
                                    'engagement' => [
                                        'label' => 'Engagement d\'impartialité',
                                        'route' => 'soutenance_hdr/engagement-impartialite',
                                        'order' => 300,
                                        'resource' => PrivilegeController::getResourceId(EngagementImpartialiteController::class, 'engagement-impartialite'),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'Acteur',
                                            'hdr'
                                        ],
                                    ],
                                    'avis' => [
                                        'label' => 'Avis de soutenance',
                                        'route' => 'soutenance_hdr/avis-soutenance',
                                        'order' => 400,
                                        'resource' => PrivilegeController::getResourceId(AvisController::class, 'index'),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'Acteur',
                                            'hdr'
                                        ],
                                    ],
                                ]
                            ],
                        ],
                    ],

                    /**
                     * Page pour Doctorant.
                     * Cette page sera dupliquée en 'ma-hdr-1', 'ma-hdr-2', etc. automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MA_HDR_PAGE_ID => [
                        'pages' => [
                            // DEPTH = 2
                            'soutenance_hdr' => [
                                'order' => 55,
                                'label' => 'Soutenance',
                                'route' => 'soutenance_hdr/proposition',
                                'params' => [
                                    'hdr' => 0
                                ],
                                'icon' => 'fas fa-chalkboard-teacher',
                                'resource' => PresoutenancePrivileges::getResourceId(PropositionPrivileges::PROPOSITION_VISUALISER),
                                'pages' => $soutenancePages,
                            ],
                        ],
                    ],

                    /**
                     * Page pour Dir, Codir.
                     * Cette page aura des pages filles 'hdr-1', 'hdr-2', etc. générées automatiquement.
                     * @see ApplicationNavigationFactory::processPage()
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::MES_HDR_PAGE_ID => [
                        'pages' => [
                            // Déclinée en 'hdr-1', 'hdr-2', etc.
                            // DEPTH = 2
                            'HDR' => [
                                'pages' => [
                                    // DEPTH = 3
                                    'soutenance_hdr' => [
                                        'order' => 50,
                                        'label' => 'Soutenance',
                                        'route' => 'soutenance_hdr/proposition',
                                        'params' => [
                                            'hdr' => 0
                                        ],
                                        'icon' => 'fas fa-chalkboard-teacher',
                                        'resource' => PresoutenancePrivileges::getResourceId(PropositionPrivileges::PROPOSITION_VISUALISER),
                                        'pages' => $soutenancePages,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            //service
            MembreService::class => MembreServiceFactory::class,
            SoutenanceNotificationFactory::class => SoutenanceNotificationFactoryFactory::class,
            ValidationTheseService::class => ValidationTheseServiceFactory::class,
            ValidationHDRService::class => ValidationHDRServiceFactory::class,
            UrlService::class => UrlServiceFactory::class,
        ],
    ],

    'controllers' => [
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
            '080_soutenance' => '/css/soutenance.css',
        ],
    ],
];
