<?php

use Application\Provider\Privilege\IndicateurPrivileges;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Indicateur\Controller\Factory\IndicateurControllerFactory;
use Indicateur\Controller\IndicateurController;
use Indicateur\Service\Factory\IndicateurServiceFactory;
use Indicateur\Service\IndicateurService;
use Indicateur\View\Helper\CompletIndicateurIndividuHelper;
use Indicateur\View\Helper\CompletIndicateurTheseHelper;
use Indicateur\View\Helper\ResumeIndicateurIndividuHelper;
use Indicateur\View\Helper\ResumeIndicateurTheseHelper;
use Zend\Mvc\Router\Http\Segment;

return array(
    'bjyauthorize'    => [
        'guards' => [
            \UnicaenAuth\Guard\PrivilegeController::class => [
                [
                    'controller' => IndicateurController::class,
                    'action'     => [
                        'index',
                        'view',
                    ],
                    'privileges' => [
                        IndicateurPrivileges::INDICATEUR_CONSULTATION,

                    ],
                ],
                [
                    'controller' => IndicateurController::class,
                    'action'     => [
                        'export',
                    ],
                    'privileges' => [
                        IndicateurPrivileges::INDICATEUR_EXPORTATION,
                    ],
                ],
            ],
        ],
    ],
    'doctrine'     => [
        'driver'     => [
            'orm_default'        => [
                'class'   => MappingDriverChain::class,
                'drivers' => [
                    'Indicateur\Model' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Indicateur/Model/Mapping',
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
                    'admin' => [
                        'pages' => [
                            'indicateur' => [
                                'label'    => 'Indicateurs',
                                'route'    => 'indicateur',
                                'resource' => IndicateurPrivileges::getResourceId(IndicateurPrivileges::INDICATEUR_CONSULTATION),
                                'order'    => 1,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'router' => [
        'routes' => [
            'indicateur' => [
                'type' => Segment::class,
                'may_terminate' => true,
                'options' => [
                    'route'    => '/indicateur',
                    'defaults' => [
                        'controller' => IndicateurController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes'  => [
                    'view' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/view/:indicateur',
                            'defaults' => [
                                'controller' => IndicateurController::class,
                                'action'     => 'view',
                            ],
                        ],
                    ],
                    'export' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/export/:indicateur',
                            'defaults' => [
                                'controller' => IndicateurController::class,
                                'action'     => 'export',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'service_manager' => [
        'factories' => [
            IndicateurService::class => IndicateurServiceFactory::class,
        ],

    ],
    'controllers' => [
        'factories' => [
            IndicateurController::class => IndicateurControllerFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'completIndicateurThese'    => CompletIndicateurTheseHelper::class,
            'completIndicateurIndividu' => CompletIndicateurIndividuHelper::class,
            'resumeIndicateurThese'     => ResumeIndicateurTheseHelper::class,
            'resumeIndicateurIndividu'  => ResumeIndicateurIndividuHelper::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
);
