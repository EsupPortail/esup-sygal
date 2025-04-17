<?php

namespace Validation;

use Validation\Service\ValidationFormationExterne\ValidationFormationExterneService;
use Validation\Service\ValidationFormationExterne\ValidationFormationExterneServiceFactory;

return [
    'service_manager' => [
        'factories' => [
            ValidationFormationExterneService::class => ValidationFormationExterneServiceFactory::class,
        ],
    ],
    'view_helpers' => [

    ],
];
