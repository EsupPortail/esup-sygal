<?php

namespace Validation;

use Validation\Service\ValidationThese\ValidationTheseService;
use Validation\Service\ValidationThese\ValidationTheseServiceFactory;
use Validation\View\Helper\ValidationTheseViewHelper;

return [
    'service_manager' => [
        'factories' => [
            ValidationTheseService::class => ValidationTheseServiceFactory::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'validationThese' => ValidationTheseViewHelper::class,
        ],
    ],
];
