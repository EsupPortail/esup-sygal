<?php

namespace Soutenance;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Soutenance\Controller\Avis\AvisController;
use Soutenance\Controller\ConfigurationController;
use Soutenance\Controller\EngagementImpartialite\EngagementImpartialiteController;
use Soutenance\Controller\Factory\ConfigurationControllerFactory;
use Soutenance\Form\Configuration\ConfigurationForm;
use Soutenance\Form\Configuration\ConfigurationFormFactory;
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
                [
                    'controller' => ConfigurationController::class,
                    'action'     => [
                        'index',
                    ],
                    'privileges' => QualitePrivileges::SOUTENANCE_QUALITE_MODIFIER,
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
                                'order'    => 100,
                                'resource' => PresoutenancePrivileges::getResourceId(PropositionPrivileges::PROPOSITION_VISUALISER),
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
                            'qualite' => [
                                'label'    => 'Qualités des membres',
                                'route'    => 'qualite',
                                'order'    => 1000,
                                'resource' => QualitePrivileges::getResourceId(QualitePrivileges::SOUTENANCE_QUALITE_VISUALISER),
                            ],
                            'configuration' => [
                                'label'    => 'Paramétrage du module de soutenance',
                                'route'    => 'configuration',
                                'order'    => 2000,
                                'resource' => QualitePrivileges::getResourceId(QualitePrivileges::SOUTENANCE_QUALITE_VISUALISER),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
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
            ConfigurationController::class => ConfigurationControllerFactory::class,
        ],
    ],

    'form_elements' => [
        'factories' => [
            ConfigurationForm::class => ConfigurationFormFactory::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
);
