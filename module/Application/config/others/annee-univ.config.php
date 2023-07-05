<?php

namespace Application;

use Application\Service\AnneeUniv\AnneeUnivService;
use Application\Service\AnneeUniv\AnneeUnivServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            AnneeUnivService::class => AnneeUnivServiceFactory::class,
        ],
    ],
];
