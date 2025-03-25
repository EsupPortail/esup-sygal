<?php

use Laminas\Log\Logger;
use Laminas\Log\PsrLoggerAbstractAdapterFactory;

return [
    'service_manager' => [
        'abstract_factories' => [
            PsrLoggerAbstractAdapterFactory::class,
        ],
    ],
    'psr_log' => [
        /**
         * Logger par défaut, cf.
         */
        'inscription_resource_logger' => [
            'writers' => [
                'stream' => [
                    'name' => 'stream',
                    'priority' => 1,
                    'options' => [
                        'stream' => '/dev/null',
                        'filters' => Logger::INFO,
                    ],
                ],
            ],
        ],
    ],
];
