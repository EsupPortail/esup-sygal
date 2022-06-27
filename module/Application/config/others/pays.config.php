<?php

use Application\Service\Pays\PaysService;
use Application\Service\Pays\PaysServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            PaysService::class => PaysServiceFactory::class,
        ],
    ],
];
