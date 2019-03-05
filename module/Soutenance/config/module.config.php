<?php

namespace Soutenance;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Soutenance\Controller\ConfigurationController;
use Soutenance\Controller\Factory\ConfigurationControllerFactory;
use Soutenance\Controller\Factory\QualiteControllerFactory;
use Soutenance\Controller\Factory\SoutenanceControllerFactory;
use Soutenance\Controller\QualiteController;
use Soutenance\Controller\SoutenanceController;
use Soutenance\Provider\Privilege\EngagementImpartialitePrivileges;
use Soutenance\Provider\Privilege\PresoutenancePrivileges;
use Soutenance\Provider\Privilege\PropositionPrivileges;
use Soutenance\Provider\Privilege\QualitePrivileges;
use Soutenance\Service\Membre\MembreService;
use Soutenance\Service\Membre\MembreServiceFactory;
use Soutenance\Service\Notifier\NotifierSoutenanceService;
use Soutenance\Service\Notifier\NotifierSoutenanceServiceFactory;
use Soutenance\Service\Parametre\ParametreService;
use Soutenance\Service\Parametre\ParametreServiceFactory;
use Soutenance\Service\Validation\ValidationService;
use Soutenance\Service\Validation\ValidationServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;
use UnicaenAuth\Provider\Rule\PrivilegeRuleProvider;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

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
        'guards' => [
                PrivilegeController::class => [
                // Consitution et validations du jury
                [
                    'controller' => SoutenanceController::class,
                    'action'     => [
                        'index',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => SoutenanceController::class,
                        'action'     => [
                            'avancement',
                        ],
                    'privileges' => PropositionPrivileges::PROPOSITION_VISUALISER,
                ],
                // Qualite
                [
                    'controller' => QualiteController::class,
                    'action'     => [
                        'index',
                    ],
                    'privileges' => QualitePrivileges::SOUTENANCE_QUALITE_VISUALISER,
                ],
                [
                    'controller' => QualiteController::class,
                    'action'     => [
                        'editer',
                        'effacer',
                    ],
                    'privileges' => QualitePrivileges::SOUTENANCE_QUALITE_MODIFIER,
                ],
                [
                    'controller' => ConfigurationController::class,
                    'action'     => [
                        'index',
                    ],
                    'roles' => [ "Administrateur technique" ],
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
                    'soutenance' => [
                        'order'    => -90,
                        'label'    => 'Soutenance',
                        'route'    => 'soutenance',
                        'roles' => [],
                        'pages' => [
                            'proposition' => [
                                'label'    => 'Proposition de soutenance',
                                'route'    => 'soutenance/proposition',
//                                'icon'     => 'glyphicon glyphicon-briefcase',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'privileges' => [
                                    PropositionPrivileges::PROPOSITION_VISUALISER,
                                ],
                            ],
                            'presoutenance' => [
                                'label'    => 'Finalisation de la soutenance',
                                'route'    => 'soutenance/presoutenance',
//                                'icon'     => 'glyphicon glyphicon-briefcase',
                                'withtarget' => true,
                                'paramsInject' => [
                                    'these',
                                ],
                                'privileges' => [
                                    PresoutenancePrivileges::PRESOUTENANCE_PRESOUTENANCE_VISUALISATION,
                                ],
                                'pages' => [
                                    'association' => [
                                        'label'    => 'Association d\'un acteur SyGAL',
                                        'route'    => 'soutenance/presoutenance/associer-jury',
//                                        'icon'     => 'glyphicon glyphicon-briefcase',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                            'membre',
                                        ],
                                        'privileges' => [
                                            PresoutenancePrivileges::PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU,
                                        ],
                                    ],
                                    'deassociation' => [
                                        'label'    => 'Casser l\'association d\'un acteur SyGAL',
                                        'route'    => 'soutenance/presoutenance/associer-jury',
//                                        'icon'     => 'glyphicon glyphicon-briefcase',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                            'membre',
                                        ],
                                        'privileges' => [
                                            PresoutenancePrivileges::PRESOUTENANCE_ASSOCIATION_MEMBRE_INDIVIDU
                                        ],
                                    ],
                                    'engagement' => [
                                        'label'    => 'Engagement d\'impartialité',
                                        'route'    => 'soutenance/presoutenance/engagement-impartialite',
//                                        'icon'     => 'glyphicon glyphicon-briefcase',
                                        'withtarget' => true,
                                        'paramsInject' => [
                                            'these',
                                            'membre',
                                        ],
                                        'privileges' => [
                                            EngagementImpartialitePrivileges::ENGAGEMENT_IMPARTIALITE_VISUALISER,
                                        ],
                                    ],
                                ],
                            ],
                            'qualite' => [
                                'label'    => 'Qualités des membres',
                                'route'    => 'qualite',
//                                'icon'     => 'glyphicon glyphicon-briefcase',
                                'privileges' => [
                                    QualitePrivileges::SOUTENANCE_QUALITE_VISUALISER,
                                ],
                            ],
                            'configuration' => [
                                'label'    => 'Paramétrage du module de soutenance',
                                'route'    => 'configuration',
//                                'icon'     => 'glyphicon glyphicon-briefcase',
                                'roles' => [
                                    "Administrateur technique"
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [

            'soutenance' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/soutenance[/:these]',
                    'defaults' => [
                        'controller' => SoutenanceController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes' => [
                    'avancement' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/avancement',
                            'defaults' => [
                                'controller' => SoutenanceController::class,
                                'action'     => 'avancement',
                            ],
                        ],
                        'child_routes' => [],
                    ],
                ],
            ],
            'configuration' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/configuration',
                    'defaults' => [
                        'controller' => ConfigurationController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'qualite' => [
                'type' => Literal::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/qualite',
                    'defaults' => [
                        'controller' => QualiteController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes' => [
                    'editer' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/editer[/:qualite]',
                            'defaults' => [
                                'controller' => QualiteController::class,
                                'action'     => 'editer',
                            ],
                        ],
                    ],
                    'effacer' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/effacer/:qualite',
                            'defaults' => [
                                'controller' => QualiteController::class,
                                'action'     => 'effacer',
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
            ParametreService::class => ParametreServiceFactory::class,
            NotifierSoutenanceService::class => NotifierSoutenanceServiceFactory::class,
            ValidationService::class => ValidationServiceFactory::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
        ],
        'factories' => [
            SoutenanceController::class => SoutenanceControllerFactory::class,
            QualiteController::class => QualiteControllerFactory::class,
            ConfigurationController::class => ConfigurationControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
        ],
    ],

    'hydrators' => [
        'invokables' => [
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
);
