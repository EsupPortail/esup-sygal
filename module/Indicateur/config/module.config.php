<?php

use Indicateur\Provider\Privilege\IndicateurPrivileges;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Indicateur\Controller\Factory\IndicateurControllerFactory;
use Indicateur\Controller\IndicateurController;
use Indicateur\Form\IndicateurForm;
use Indicateur\Form\IndicateurFormFactory;
use Indicateur\Form\IndicateurHydrator;
use Indicateur\Service\Factory\IndicateurServiceFactory;
use Indicateur\Service\IndicateurService;
use Indicateur\View\Helper\CompletIndicateurIndividuHelper;
use Indicateur\View\Helper\CompletIndicateurStructureHelper;
use Indicateur\View\Helper\CompletIndicateurTheseHelper;
use Indicateur\View\Helper\ResumeIndicateurIndividuHelper;
use Indicateur\View\Helper\ResumeIndicateurStructureHelper;
use Indicateur\View\Helper\ResumeIndicateurTheseHelper;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;

return array(
    'bjyauthorize'    => [
        'guards' => [
            \UnicaenPrivilege\Guard\PrivilegeController::class => [
                [
                    'controller' => IndicateurController::class,
                    'action'     => [
                        'index',
                        'view',
                        'toggle-indicateur',
                        'lister-indicateur',
                        'editer-indicateur',
                        'effacer-indicateur',
                        'rafraichir-indicateur',
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
                                'icon' => "fas fa-chart-bar",
                                'order'    => 1070,
                            ],
                            '--------autres-divider' => [
                                'label' => null,
                                'order' => 1080,
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
                    'editer' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/editer[/:indicateur]',
                            'defaults' => [
                                'controller' => IndicateurController::class,
                                'action'     => 'editer-indicateur',
                            ],
                        ],
                    ],
                    'lister' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/lister',
                            'defaults' => [
                                'controller' => IndicateurController::class,
                                'action'     => 'lister-indicateur',
                            ],
                        ],
                    ],
                    'toggle' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/toggle/:indicateur',
                            'defaults' => [
                                'controller' => IndicateurController::class,
                                'action'     => 'toggle-indicateur',
                            ],
                        ],
                    ],
                    'rafraichir' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/rafraichir/:indicateur',
                            'defaults' => [
                                'controller' => IndicateurController::class,
                                'action'     => 'rafraichir-indicateur',
                            ],
                        ],
                    ],
                    'effacer' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/effacer/:indicateur',
                            'defaults' => [
                                'controller' => IndicateurController::class,
                                'action'     => 'effacer-indicateur',
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

    'form_elements' => [
        'factories' => [
            IndicateurForm::class => IndicateurFormFactory::class,
        ],
    ],
    'hydrators' => [
        'invokables' => [
            IndicateurHydrator::class => IndicateurHydrator::class,
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
            'completIndicateurThese'     => CompletIndicateurTheseHelper::class,
            'completIndicateurIndividu'  => CompletIndicateurIndividuHelper::class,
            'completIndicateurStructure' => CompletIndicateurStructureHelper::class,
            'resumeIndicateurThese'      => ResumeIndicateurTheseHelper::class,
            'resumeIndicateurIndividu'   => ResumeIndicateurIndividuHelper::class,
            'resumeIndicateurStructure'  => ResumeIndicateurStructureHelper::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
);
