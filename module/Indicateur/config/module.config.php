<?php

use Application\Provider\Privilege\IndicateurPrivileges;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Indicateur\Controller\Factory\IndicateurControllerFactory;
use Indicateur\Controller\IndicateurController;
use Indicateur\Service\Factory\IndicateurServiceFactory;
use Indicateur\Service\IndicateurService;
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
                        'soutenance-depassee',
                        'acteurs-sans-mail',
                        'doctorants-sans-mail',
                        'theses-anciennes',
                        'theses-a-soutenir',
                        'theses-sans-pdc',
                        'theses-sans-depot',
                ],
                    'privileges' => [
                        IndicateurPrivileges::INDICATEUR_CONSULTATION,

                    ],
                ],
                [
                    'controller' => IndicateurController::class,
                    'action'     => [
                        'export-soutenance-depassee',
                        'export-acteurs-sans-mail',
                        'export-doctorants-sans-mail',
                        'export-theses-anciennes',
                        'export-theses-a-soutenir',
                        'export-theses-sans-pdc',
                        'export-theses-sans-depot',
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
                    'soutenance-depassee' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/soutenance-depassee',
                            'defaults'    => [
                                'action' => 'soutenance-depassee',
                            ],
                        ],
                        'child_routes'  => [
                            'export' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/export',
                                    'defaults'    => [
                                        'action' => 'export-soutenance-depassee',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'acteurs-sans-mail' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/acteurs-sans-mail',
                            'defaults'    => [
                                'action' => 'acteurs-sans-mail',
                            ],
                        ],
                        'child_routes'  => [
                            'export' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/export',
                                    'defaults'    => [
                                        'action' => 'export-acteurs-sans-mail',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'theses-a-soutenir' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/theses-a-soutenir',
                            'defaults'    => [
                                'action' => 'theses-a-soutenir',
                            ],
                        ],
                        'child_routes'  => [
                            'export' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/export',
                                    'defaults'    => [
                                        'action' => 'export-theses-a-soutenir',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'theses-sans-pdc' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/theses-sans-pdc',
                            'defaults'    => [
                                'action' => 'theses-sans-pdc',
                            ],
                        ],
                        'child_routes'  => [
                            'export' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/export',
                                    'defaults'    => [
                                        'action' => 'export-theses-sans-pdc',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'theses-sans-depot' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/theses-sans-depot',
                            'defaults'    => [
                                'action' => 'theses-sans-depot',
                            ],
                        ],
                        'child_routes'  => [
                            'export' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/export',
                                    'defaults'    => [
                                        'action' => 'export-theses-sans-depot',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'doctorants-sans-mail' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/doctorants-sans-mail',
                            'defaults'    => [
                                'action' => 'doctorants-sans-mail',
                            ],
                        ],
                        'child_routes'  => [
                            'export' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/export',
                                    'defaults'    => [
                                        'action' => 'export-doctorants-sans-mail',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'theses-anciennes' => [
                        'type'          => Segment::class,
                        'may_terminate' => true,
                        'options'       => [
                            'route'       => '/theses-anciennes',
                            'defaults'    => [
                                'action' => 'theses-anciennes',
                            ],
                        ],
                        'child_routes'  => [
                            'export' => [
                                'type'          => Segment::class,
                                'may_terminate' => true,
                                'options'       => [
                                    'route'       => '/export',
                                    'defaults'    => [
                                        'action' => 'export-theses-anciennes',
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
            'resumeIndicateurThese' => ResumeIndicateurTheseHelper::class,
            'resumeIndicateurIndividu' => ResumeIndicateurIndividuHelper::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
);
