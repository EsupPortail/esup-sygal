<?php

use Doctrine\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Notification\Entity\Service\NotifEntityService;
use Notification\Entity\Service\NotifEntityServiceFactory;
use Notification\Service\NotificationRenderingService;
use Notification\Service\NotificationRenderingServiceFactory;
use Notification\Service\NotifierService;
use Notification\Service\NotifierServiceFactory;

return [
    'doctrine'     => [
        /**
         * Génération du mapping à partir de la bdd, exemple :
         *   $ vendor/bin/doctrine-module orm:convert-mapping --namespace="Application\\Entity\\Db\\" --filter="Version" --from-database xml module/Application/src/Application/Entity/Db/Mapping
         *
         * Génération des classes d'entité, exemple :
         *   $ vendor/bin/doctrine-module orm:generate:entities --filter="Version" module/Application/src
         */
        'driver'     => [
            'orm_default'        => [
                'class'   => MappingDriverChain::class,
                'drivers' => [
                    'Notification\Entity' => 'orm_default_xml_driver',
                ],
            ],
            'orm_default_xml_driver' => [
                'class' => XmlDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__ . '/../src/Notification/Entity/Mapping',
                ],
            ],
        ],
    ],
    'router' => [
        'routes' => [
        ],
    ],
    'service_manager' => [
        'invokables' => [
        ],
        'factories' => [
            NotificationRenderingService::class => NotificationRenderingServiceFactory::class,
            NotifEntityService::class           => NotifEntityServiceFactory::class,
            NotifierService::class              => NotifierServiceFactory::class,
        ],
        'aliases' => [
        ],
        'initializers' => [
        ]
    ],
    'controllers' => [
        'invokables' => [
        ],
        'initializers' => [
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];