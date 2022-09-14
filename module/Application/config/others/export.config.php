<?php

use Application\Controller\Factory\ExportControllerFactory;
use These\Provider\Privilege\ThesePrivileges;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'bjyauthorize'    => [
        'guards' => [
            PrivilegeController::class => [
                [
                    'controller' => 'Application\Controller\Export',
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
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller'    => 'Export',
                    ],
                ],
                'may_terminate' => false,
                'child_routes'  => [
                    'csv' => [
                        'type'    => 'Segment',
                        'options' => [
                            'route'    => '/csv',
                            'defaults' => [
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
            'Application\Controller\Export' => ExportControllerFactory::class,
        ],
    ],
];