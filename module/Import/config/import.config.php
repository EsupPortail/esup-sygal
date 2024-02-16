<?php

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Import\Controller\Factory\ImportObserverControllerFactory;
use Import\Controller\ImportController;
use Import\Model\Service\ImportObservResultService;
use Import\Model\Service\ImportObservResultServiceFactory;
use Unicaen\Console\Router\Simple;
use Laminas\Router\Http\Segment;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => Import\Controller\ImportController::class,
                    'action'     => [
                        'update-these',
                    ],
                    'roles' => [
                        'Administrateur technique',
                    ],
                ],
                [
                    'controller' => Import\Controller\ImportController::class,
                    'action'     => [
                        'update-these-console',
                    ],
                    'roles' => [],
                ],
                [
                    'controller' => 'Application\Controller\ImportNotification',
                    'action'     => [
                        'process-observed-import-results',
                    ],
                    'roles' => [],
                ],
            ],
        ],
    ],

    'doctrine'     => [
        'driver'     => [
            'orm_default'        => [
                'class'   => MappingDriverChain::class,
                'drivers' => [
                    'UnicaenDbImport\Entity\Db\ImportObserv' => 'orm_default_xml_driver', // remplacement du mapping
                    'UnicaenDbImport\Entity\Db\ImportObservResult' => 'orm_default_xml_driver', // idem
                    'Import\Model' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Import/Model/Mapping',
                ],
            ],
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [
                'update-these-console' => [
                    'type' => Simple::class,
                    'options' => [
                        'route'    => 'update-these --id= [--verbose] [--em=]',
                        'defaults' => [
                            /**
                             * @see ImportController::updateTheseConsoleAction()
                             */
                            'controller' => Import\Controller\ImportController::class,
                            'action'     => 'update-these-console',
                        ],
                    ],
                ],
                'process-observed-import-results' => [
                    'options' => [
                        'route'    => 'process-observed-import-results --source= [--import-observ=] [--source-code=]',
                        'defaults' => [
                            /**
                             * @see \Import\Controller\ImportObserverController::processObservedImportResultsAction()
                             */
                            'controller' => 'Application\Controller\ImportNotification',
                            'action'     => 'process-observed-import-results',
                        ],
                    ],
                ],
            ],
        ],
        'view_manager' => [
            'display_not_found_reason' => true,
            'display_exceptions'       => true,
        ]
    ],

    'router' => [
        'routes' => [
            'ws-import' => [
                'type' => 'Literal',
                'may_terminate' => true,
                'options' => [
                    'route'    => '/ws-import',
                    'defaults' => [
                        'controller' => Import\Controller\ImportController::class,
                        'action'     => 'index',
                    ],
                ],
                'child_routes' => [
                    'update-these' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/update-these/:etablissement[/:source_code]',
                            'defaults' => [
                                'controller' => Import\Controller\ImportController::class,
                                'action'     => 'update-these',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                ],
            ],
        ],
    ],

    'service_manager' => [
        'abstract_factories' => [
            'Laminas\Cache\Service\StorageCacheAbstractServiceFactory',
            'Laminas\Log\LoggerAbstractServiceFactory',
        ],
        'factories' => [
            Import\Service\ImportService::class  => Import\Service\Factory\ImportServiceFactory::class,
            ImportObservResultService::class => ImportObservResultServiceFactory::class,
        ],
        'aliases' => [
            'ImportService' => Import\Service\ImportService::class,
        ]

    ],

    'controllers' => [
        'factories' => [
            Import\Controller\ImportController::class => Import\Controller\Factory\ImportControllerFactory::class,
            'Application\Controller\ImportNotification' => ImportObserverControllerFactory::class,
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
