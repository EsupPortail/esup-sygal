<?php

use Application\Service\Source\SourceService;
use Application\Service\Source\SourceServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            SourceService::class => SourceServiceFactory::class,
        ],
        'aliases' => [
            'SourceService' => SourceService::class,
        ]
    ],
];
