<?php

namespace Validation;

use Validation\Service\ValidationHDR\ValidationHDRService;
use Validation\Service\ValidationHDR\ValidationHDRServiceFactory;
use Validation\View\Helper\ValidationHDRViewHelper;

return [
    'service_manager' => [
        'factories' => [
            ValidationHDRService::class => ValidationHDRServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'validationHDR' => ValidationHDRViewHelper::class,
        ],
    ],
];
