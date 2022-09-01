<?php

namespace Fichier;

use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Fichier\Controller\ConsoleController;
use Fichier\Controller\Factory\ConsoleControllerFactory;
use Fichier\Controller\IndexController;
use Fichier\Controller\Factory\IndexControllerFactory;
use Laminas\Mvc\Console\Router\Simple;

return [
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'Fichier\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Fichier/Entity/Db/Mapping',
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'application' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/fichier',
                    'defaults' => [
                        'controller' => IndexController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [

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
    'bjyauthorize' => [
        'guards' => [

        ],
    ],
    'service_manager' => [
        'factories' => [

        ],
    ],
    'controllers' => [
        'factories' => [
            IndexController::class => IndexControllerFactory::class,
            ConsoleController::class => ConsoleControllerFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];