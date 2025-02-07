<?php

use Application\Controller\ExportController;
use Application\Controller\Factory\ExportControllerFactory;
use Application\Export\Xlsx\Writer\ExcelDataTables\ExcelDataTablesXlsxWriter;
use Application\Export\Xlsx\Writer\PhpOffice\PhpOfficeXlsxWriter;
use Application\Export\Xlsx\Writer\XlsxWriterManager;
use Application\Export\Xlsx\Writer\XlsxWriterManagerFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;
use These\Provider\Privilege\ThesePrivileges;
use UnicaenPrivilege\Guard\PrivilegeController;

return [
    'xlsx_writer' => [
        'factories' => [
            // Cf. https://github.com/PHPOffice/PhpSpreadsheet
            // Attention : détruit les éventuels tableaux croisés existants !
            PhpOfficeXlsxWriter::class => InvokableFactory::class,
            // Cf. https://github.com/svrnm/exceldatatables
            // Injection de données brutes dans un .xlsx contenant des tableaux dynamiques.
            ExcelDataTablesXlsxWriter::class => InvokableFactory::class,
        ],
    ],
    'xlsx_export' => [
        'factories' => [],
        'aliases' => [],
        'config' => [],
    ],

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
            XlsxWriterManager::class => XlsxWriterManagerFactory::class,
        ],
    ],
    'controllers'     => [
        'factories' => [
            ExportController::class => ExportControllerFactory::class,
        ],
    ],
];