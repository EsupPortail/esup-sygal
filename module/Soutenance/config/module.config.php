<?php

namespace Soutenance;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Soutenance\Controller\Avis\AvisController;
use Soutenance\Controller\EngagementImpartialite\EngagementImpartialiteController;
use Soutenance\Controller\Presoutenance\PresoutenanceController;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Membre\MembreServiceFactory;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceFactory;
use Soutenance\Service\Validation\ValidationService;
use Soutenance\Service\Validation\ValidationServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;

return array(
    'bjyauthorize'    => [
        'resource_providers' => [
            'BjyAuthorize\Provider\Resource\Config' => [
                'Acteur' => [],
            ],
        ],
        'rule_providers'     => [
            PrivilegeRuleProvider::class => [
                'allow' => [
                ],
            ],
        ],
    ],

    'doctrine'     => [
        'driver'     => [
            'orm_default'        => [
                'class'   => MappingDriverChain::class,
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
        'connection'    => [
            'orm_default' => [
                'driver_class' => OCI8::class,
            ],
        ],
    ],

    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'depot' => [
                        'order'    => -98,
                        'label'    => 'Dépôt',
                        'route'    => 'home',
                    ],
                    'soutenance' => [
                        'order'    => -99,
                        'label'    => 'Soutenance',
                        'route'    => 'soutenance',
                        'roles' => [],
                        'pages' => [
                            'proposition' => [
                                'label'    => 'Proposition de soutenance',
                                'route'    => 'soutenance/proposition',
                                'order'    => 100,
                                'resource' => PresoutenancePrivileges::getResourceId(PropositionPrivileges::PROPOSITION_VISUALISER),
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                            ],
                            'simulation' => [
                                'label'    => 'Simulation SI',
                                'route'    => 'simulation',
                                'order'    => 150,
                                'resource' => PresoutenancePrivileges::getResourceId(PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION),
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                            ],
                            'presoutenance' => [
                                'label'    => 'Étape de présoutenance',
                                'route'    => 'soutenance/presoutenance',
                                'order'    => 200,
                                'resource' => PresoutenancePrivileges::getResourceId(PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION),
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                            ],
                            'engagement' => [
                                'label' => 'Engagement d\'impartialité',
                                'route' => 'soutenance/engagement-impartialite',
                                'order'    => 300,
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
                                'order'    => 400,
                                'resource' => PrivilegeController::getResourceId(AvisController::class, 'index'),
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                    'Acteur',
                                ],
                            ],
                            'retard' => [
                                'label' => 'Notifier attente de rapport',
                                'route' => 'soutenance/notifier-retard-rapport-presoutenance',
                                'order'    => 500,
//                                'resource' => PrivilegeController::getResourceId(PresoutenanceController::class, 'notifier-retard-rapport-presoutenance'),
                                'privileges' => PresoutenancePrivileges::PRESOUTENANCE_DATE_RETOUR_MODIFICATION,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

//    'navigation_helpers' => [
//        'factories' => [
//            'menuPiedDePage'      => MenuPiedDePageFactory::class,
//            'menuSecondaire'      => MenuSecondaireFactory::class,
//        ],
//    ],

    'service_manager' => [
        'factories' => [
            //service
            MembreService::class => MembreServiceFactory::class,
            NotifierSoutenanceService::class => NotifierSoutenanceServiceFactory::class,
            ValidationService::class => ValidationServiceFactory::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],

    'public_files' => [
        'inline_scripts' => [
            '114_' => 'vendor/bootstrap-select-1.13.9/dist/js/bootstrap-select.min.js',
        ],
        'stylesheets' => [
            '114_' => 'vendor/bootstrap-select-1.13.9/dist/css/bootstrap-select.min.css',
        ],
    ],
);
