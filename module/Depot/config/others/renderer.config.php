<?php

namespace Depot;

use Depot\Renderer\DiffusionTemplateVariable;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'renderer' => [
        'template_variables' => [
            'factories' => [
                DiffusionTemplateVariable::class => InvokableFactory::class,
            ],
            'aliases' => [
                'diffusion' => DiffusionTemplateVariable::class,
            ],
        ],
    ]
];