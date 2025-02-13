<?php

namespace Validation;

use Validation\Service\ValidationService;
use Validation\Service\ValidationServiceFactory;
use Validation\View\Helper\ValidationViewHelper;

return [
    'service_manager' => [
        'factories' => [
            ValidationService::class => ValidationServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'validation' => ValidationViewHelper::class,
        ],
    ],
];
