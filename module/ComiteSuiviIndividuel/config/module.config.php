<?php

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use UnicaenAuth\Guard\PrivilegeController;

return [
    'doctrine' => [
        'driver' => [
            'orm_default' => [
                'class' => MappingDriverChain::class,
                'drivers' => [
                    'ComiteSuiviIndividuel\Entity\Db' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/ComiteSuiviIndividuel/Entity/Db/Mapping',
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'bjyauthorize' => [
//        'rule_providers'     => [
//            PrivilegeRuleProvider::class => [
//                'allow' => [
//                    [],
//                ],
//            ],
//        ],
        'guards' => [
            PrivilegeController::class => []
        ],
    ],
    'router' => [
        'routes' => [
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
    'form_elements' => [
        'factories' => [
        ],
    ],
    'hydrators' => array(
        'factories' => array()
    ),
    'service_manager' => [
        'factories' => [
        ],
    ],
    'controllers' => [
        'factories' => [
        ],
    ],
    'controller_plugins' => [
    ],
];
