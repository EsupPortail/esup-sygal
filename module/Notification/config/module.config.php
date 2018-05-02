<?php

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Notification\Service\Mailer\MailerService;
use Notification\Service\Mailer\MailerServiceFactory;
use Notification\Service\NotificationService;
use Notification\Service\NotificationServiceFactory;

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
            NotificationService::class => NotificationServiceFactory::class,
            MailerService::class => MailerServiceFactory::class,
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