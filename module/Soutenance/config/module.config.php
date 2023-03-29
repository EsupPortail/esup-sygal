<?php

namespace Soutenance;

use Application\Navigation\ApplicationNavigationFactory;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Soutenance\Controller\AvisController;
use Soutenance\Controller\EngagementImpartialiteController;
use Soutenance\Controller\PropositionController;
use Soutenance\Provider\Privilege\IndexPrivileges;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Membre\MembreServiceFactory;
use Soutenance\Service\Notification\SoutenanceNotificationFactory;
use Soutenance\Service\Notification\SoutenanceNotificationFactoryFactory;
use Soutenance\Service\Url\UrlService;
use Soutenance\Service\Url\UrlServiceFactory;
use Soutenance\Service\Validation\ValidationService;
use Soutenance\Service\Validation\ValidationServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return array(
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

                    /**
                     * Navigation pour LA thèse courante.
                     */
                    // DEPTH = 1
                    ApplicationNavigationFactory::THESE_SELECTIONNEE_PAGE_ID => [
                        'pages' => [
                            // DEPTH = 2
                            'soutenance' => [
                                'order' => 55,
                                'label' => 'Soutenance',
                                'route' => 'soutenance/proposition',
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
                                        'route' => 'soutenance/proposition',
                                        'order' => 100,
                                        'resource' => PresoutenancePrivileges::getResourceId(PropositionPrivileges::PROPOSITION_VISUALISER),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                    ],
                                    'presoutenance' => [
                                        'label' => 'Préparation de la soutenance',
                                        'route' => 'soutenance/presoutenance',
                                        'order' => 200,
                                        'resource' => PresoutenancePrivileges::getResourceId(PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                    ],
                                    'horodatages' => [
                                        'label' => 'Horodatages des événements',
                                        'route' => 'soutenance/proposition/horodatages',
                                        'order' => 1000,
                                        'resource' => PresoutenancePrivileges::getResourceId(PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                        ],
                                    ],
//                                    'retard' => [
//                                        'label' => 'Notifier attente de rapport',
//                                        'route' => 'soutenance/notifier-retard-rapport-presoutenance',
//                                        'order' => 500,
//                                        'resource' => PresoutenancePrivileges::getResourceId(PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION),
//                                    ],
                                ],
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
                                'resource' => IndexPrivileges::getResourceId(IndexPrivileges::INDEX_RAPPORTEUR),
                                'child_routes' => [
                                    'engagement' => [
                                        'label' => 'Engagement d\'impartialité',
                                        'route' => 'soutenance/engagement-impartialite',
                                        'order' => 300,
                                        'resource' => PrivilegeController::getResourceId(EngagementImpartialiteController::class, 'engagement-impartialite'),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                            'Acteur',
                                        ],
                                    ],
                                    'avis' => [
                                        'label' => 'Avis de soutenance',
                                        'route' => 'soutenance/avis-soutenance',
                                        'order' => 400,
                                        'resource' => PrivilegeController::getResourceId(AvisController::class, 'index'),
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                            'Acteur',
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
                            'soutenance' => [
                                'order' => 55,
                                'label' => 'Soutenance',
                                'route' => 'soutenance/proposition',
                                'params' => [
                                    'these' => 0,
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
                                    'soutenance' => [
                                        'order' => 50,
                                        'label' => 'Soutenance',
                                        'route' => 'soutenance/proposition',
                                        'params' => [
                                            'these' => 0,
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
            ValidationService::class => ValidationServiceFactory::class,
            UrlService::class => UrlServiceFactory::class,
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
        ],
    ],
);
