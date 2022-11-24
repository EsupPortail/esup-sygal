<?php

use Application\Service\Validation\ValidationService;
use Application\View\Helper\ValidationViewHelper;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'service_manager' => [
        'factories' => [
            ValidationService::class => InvokableFactory::class,
        ],
        'aliases' => [
            'ValidationService' => ValidationService::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'validation' => ValidationViewHelper::class,
        ],
    ],
];
