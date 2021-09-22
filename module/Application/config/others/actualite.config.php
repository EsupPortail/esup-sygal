<?php

namespace Application;

use Application\Service\Actualite\ActualiteService;
use Application\Service\Actualite\ActualiteServiceFactory;
use Application\View\Helper\Actualite\ActualiteViewHelperFactory;

return [
    'service_manager' => [
        'factories' => [
            ActualiteService::class => ActualiteServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'actualite' => ActualiteViewHelperFactory::class,
        ],
    ],
];
