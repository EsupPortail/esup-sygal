<?php

use Import\Controller\Factory\ImportObserverControllerFactory;
use Import\Controller\Factory\SynchroControllerFactory;
use Import\Controller\SynchroController;
use Import\Service\ImportObserv\ImportObservService;
use Import\Service\ImportObservEtabResult\ImportObservEtabResultService;
use Import\Service\ImportObservEtabResult\ImportObservEtabResultServiceFactory;
use Import\Service\SchemaService;
use UnicaenAuth\Guard\PrivilegeController;
use Zend\Mvc\Router\Console\Simple;

return [
    'bjyauthorize'    => [
        'guards' => [
            \UnicaenAuth\Guard\PrivilegeController::class => [
                [
                    'controller' => SynchroController::class,
                    'action'     => [
                        'receive-notification',
                    ],
                    'roles' => 'guest',
                ],
                [
                    'controller' => SynchroController::class,
                    'action'     => [
                        'index',
                    ],
                    'privileges' => UnicaenImport\Provider\Privilege\Privileges::IMPORT_ECARTS,
                ],
                [
                    'controller' => SynchroController::class,
                    'action'     => [
                        'update-views-and-packages',
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
    'console' => [
        'router' => [
            'routes' => [
                'synchronizeConsole' => [
                    'type' => Simple::class,
                    'options' => [
                        'route'    => 'synchronize --service= [--em=]',
                        'defaults' => [
                            'controller' => SynchroController::class,
                            'action'     => 'synchronizeConsole',
                        ],
                    ],
                ],
                'synchronizeAllConsole' => [
                    'type' => Simple::class,
                    'options' => [
                        'route'    => 'synchronize-all [--em=]',
                        'defaults' => [
                            'controller' => SynchroController::class,
                            'action'     => 'synchronizeAllConsole',
                        ],
                    ],
                ],
                'process-observed-import-results' => [
                    'options' => [
                        'route'    => 'process-observed-import-results --etablissement= [--import-observ=] [--source-code=] [--force]',
                        'defaults' => [
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
            'import-index-new' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '/import',
                    'defaults' => [
                        'controller'    => SynchroController::class,
                        'action'        => 'index',
                    ],
                ],
                'may_terminate' => true,
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
    'navigation' => [
        'default' => [
            'home' => [
                'pages' => [
                    'import' /* NE PAS MODIFIER CETTE CLÉ */ => [
                        'label'    => 'Synchro',
                        'pages'    => [
                            'differentiel'               => [
                                'route'       => 'import-index-new',
                                'resource'    => PrivilegeController::getResourceId('Import\Controller\Import', 'index'),
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'UnicaenImport\Service\Schema' => SchemaService::class,
            ImportObservService::class     => ImportObservService::class,
        ],
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],
        'factories' => [
            ImportObservEtabResultService::class => ImportObservEtabResultServiceFactory::class,
        ],

    ],
    'controllers' => [
        'factories' => [
            'Application\Controller\ImportNotification' => ImportObserverControllerFactory::class,
            SynchroController::class => SynchroControllerFactory::class,
        ],
        'aliases' => [
            'Import\Controller\Import' => SynchroController::class, // remplacement de celui d'UnicaenImport
        ]
    ],
    'view_manager' => [
        'template_map'             => [
//            'import/synchro/tableau-bord' =>
//                __DIR__ . '/../../../vendor/unicaen/import/view/unicaen-import/import/tableau-bord.phtml',
//            'import/synchro/update-views-and-packages' =>
//                __DIR__ . '/../../../vendor/unicaen/import/view/unicaen-import/import/update-views-and-packages.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
