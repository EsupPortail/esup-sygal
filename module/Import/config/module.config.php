<?php

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\DBAL\Driver\OCI8\Driver as OCI8;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Zend\Mvc\Router\Console\Simple;
use Zend\Mvc\Router\Http\Literal;
use Zend\Mvc\Router\Http\Segment;

return array(
    'bjyauthorize'    => [
        'guards' => [
            \UnicaenAuth\Guard\PrivilegeController::class => [
                [
                    'controller' => Import\Controller\ImportController::class,
                    'action'     => [
                        'help',
                        'fetch',
                    ],
                    'roles' => [
                        'Administrateur technique',
                    ],
                ],
                [
                    'controller' => Import\Controller\ImportController::class,
                    'action'     => [
                        'fetch-console',
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
        'connection'    => [
            'orm_default' => [
                'driver_class' => OCI8::class,
            ],
        ],
    ],

    'console' => [
        'router' => [
            'routes' => [
                'import-console' => [
                    'type' => Simple::class,
                    'options' => [
                        'route'    => 'fetch --service=  --etablissement= [--source-code=]',
                        'defaults' => [
                            'controller' => Import\Controller\ImportController::class,
                            'action'     => 'fetch-console',
                            'service'       => 'non renseigné',
                            'etablissement' => 'non renseigné',
                            'source_code'   => 'non renseigné',
                        ],
                    ],
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'home-import' => [
                'type' => 'Literal',
                'may_terminate' => false,
                'options' => [
                    'route'    => '/fetch',
                    'defaults' => [
                        'controller' => Import\Controller\ImportController::class,
                        'action'     => 'help',
                    ],
                ],
                'child_routes' => [
                    'import' => [
                        'type' => Segment::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/:service/:etablissement[/:source_code]',
                            'defaults' => [
                                'controller'    => Import\Controller\ImportController::class,
                                'action'        => 'fetch',
                                'service'       => 'non renseigné',
                                'etablissement' => 'non renseigné',
                                'source_code'   => 'non renseigné',
                            ],
                            'constraints' => [
                                'service'       => implode ('|', ["all","these","doctorant","acteur","variable","individu","role"]),
                                'etablissement' => implode ('|', ["UCN","fOO","INSA"]),
                            ]
                        ],
                    ],
                    'help' => [
                        'type' => Literal::class,
                        'may_terminate' => true,
                        'options' => [
                            'route'    => '/help',
                            'defaults' => [
                                'controller'    => Import\Controller\ImportController::class,
                                'action'        => 'help',
                            ],
                        ],
                    ]
                ],
            ],
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],
        'factories' => [
            Import\Service\FetcherService::class => Import\Service\Factory\FetcherServiceFactory::class,
        ],

    ],
    'controllers' => [
        'factories' => [
            Import\Controller\ImportController::class => Import\Controller\Factory\ImportControllerFactory::class,
        ],
    ],
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
