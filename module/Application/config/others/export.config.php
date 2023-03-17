<?php

use Application\Controller\ExportController;
use Application\Controller\Factory\ExportControllerFactory;
use These\Provider\Privilege\ThesePrivileges;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => ExportController::class,
                    'action'     => [
                        'csv',
                    ],
                    'privileges' => ThesePrivileges::THESE_EXPORT_CSV,
                ],
            ],
        ],
    ],
    'router'          => [
        'routes' => [
            'export' => [
                'type'          => 'Literal',
                'options'       => [
                    'route'    => '/export',
                    'defaults' => [
                        'controller' => ExportController::class,
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'csv' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/csv',
                            'defaults' => [
                                /** @see ExportController::csvAction() */
                                'action' => 'csv',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
//            'ExportService' => ::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            ExportController::class => ExportControllerFactory::class,
        ],
    ],
];