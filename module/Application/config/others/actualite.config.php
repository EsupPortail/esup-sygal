<?php

namespace Application;

use Application\Service\Actualite\ActualiteService;
use Application\Service\Actualite\ActualiteServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            ActualiteService::class => ActualiteServiceFactory::class,
        ],
    ],
];
