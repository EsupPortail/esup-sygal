<?php

use HDR\Renderer\HDRTemplateVariable;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'renderer' => [
        'template_variables' => [
            'factories' => [
                HDRTemplateVariable::class => InvokableFactory::class,
            ],
            'aliases' => [
                'hdr' => HDRTemplateVariable::class,
            ],
        ],
    ]
];