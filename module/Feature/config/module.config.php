<?php

namespace Feature;

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Feature\Controller\FeatureController;
use Feature\Entity\Db\Service\FeatureService;
use Feature\Entity\Db\Service\FeatureServiceFactory;

return [
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'Feature\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Feature/Entity/Db/Mapping',
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
            'application' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/feature',
                    'defaults' => [
                        'controller' => FeatureController::class,
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
            FeatureService::class => FeatureServiceFactory::class,
        ],
    ],
    'controllers' => [
        'factories' => [
            FeatureController::class => FeatureController::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];