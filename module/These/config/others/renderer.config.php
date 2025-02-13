<?php

namespace These;

use Acteur\Renderer\ActeurTemplateVariable;
use Laminas\ServiceManager\Factory\InvokableFactory;
use These\Renderer\TheseTemplateVariable;

return [
    'renderer' => [
        'template_variables' => [
            'factories' => [
                TheseTemplateVariable::class => InvokableFactory::class,
                ActeurTemplateVariable::class => InvokableFactory::class,
            ],
            'aliases' => [
                'these' => TheseTemplateVariable::class,
                'acteur' => ActeurTemplateVariable::class,
            ],
        ],
    ]
];