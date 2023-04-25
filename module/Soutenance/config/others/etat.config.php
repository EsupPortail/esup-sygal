<?php

namespace Soutenance;

use Soutenance\View\Helper\EtatViewHelper;

return [


    'service_manager' => [
        'factories' => [
        ],
    ],
    'controllers' => [
        'factories' => [
        ],
    ],
    'form_elements' => [
        'factories' => [
        ],
    ],
    'hydrators' => [
        'factories' => [
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'etatSoutenance' => EtatViewHelper::class,
        ],
    ],
];