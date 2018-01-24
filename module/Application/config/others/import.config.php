<?php

use Application\Controller\Factory\ImportObserverControllerFactory;
use Application\Controller\ImportController;
use Application\Service\Import\SchemaService;
use Application\Service\ImportObserv\ImportObservService;
use Application\Service\ImportObservResult\ImportObservResultServiceFactory;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Import',
                    'action'     => [
                        'receive-notification',
                    ],
                    'roles' => 'guest',
                ],
                [
                    'controller' => 'Application\Controller\Import',
                    'action'     => [
                        'show-diff',
                    ],
                    'privileges' => UnicaenImport\Provider\Privilege\Privileges::IMPORT_ECARTS,
                ],
                [
                    'controller' => 'Application\Controller\Import',
                    'action'     => [
                        'update-tables',
                    ],
                    'privileges' => UnicaenImport\Provider\Privilege\Privileges::IMPORT_MAJ,
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
    'router'          => [
        'routes' => [
            'import-show-diff' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/import/show-diff[/:table]',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Import',
                        'action'        => 'show-diff',
                        'table'         => null,
                    ],
                ],
            ],
            'import-update-tables' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/import/update-tables[/:table]',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Import',
                        'action'        => 'update-tables',
                        'table'         => null,
                    ],
                ],
            ],
            'receive-notification' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/import/receive-notification/:notification',
                    'constraints'   => [
                        'notification' => '\d+',
                    ],
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Import',
                        'action'        => 'receive-notification',
                    ],
                ],
            ],
        ],
    ],
    'console' => [
        'router' => [
            'routes' => [
                'process-observed-import-results' => [
                    'options' => [
                        'route'    => 'process-observed-import-results [--force]',
                        'defaults' => [
                            'controller' => 'Application\Controller\ImportNotification',
                            'action'     => 'process-observed-import-results',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'navigation'      => [
        'default' => [
            'home' => [
                'pages' => [
                    'import' => [
                        'pages'    => [
                            'showDiff'               => [
                                'route'       => 'import-show-diff',
                                'resource'    => PrivilegeController::getResourceId('Application\Controller\Import', 'show-diff'),
                                'params'      => [
                                    'action' => 'show-diff',
                                ],
                            ],
                            'updateTables'               => [
                                'route'       => 'import-update-tables',
                                'resource'    => PrivilegeController::getResourceId('Application\Controller\Import', 'update-tables'),
                                'params'      => [
                                    'action' => 'update-tables',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => array(
            'UnicaenImport\Service\Schema' => SchemaService::class,
            'ImportObservService'          => ImportObservService::class,
        ),
        'factories' => [
            'ImportObservResultService'    => ImportObservResultServiceFactory::class,
        ],
    ],
    'controllers'     => [
        'invokables' => [
            'Application\Controller\Import' => ImportController::class,
        ],
        'factories' => [
            'Application\Controller\ImportNotification' => ImportObserverControllerFactory::class,
        ],
    ],
];