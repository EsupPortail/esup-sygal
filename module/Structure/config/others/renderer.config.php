<?php

namespace Structure;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Structure\Renderer\StructureTemplateVariable;

return [
    'renderer' => [
        'template_variables' => [
            'factories' => [
                StructureTemplateVariable::class => InvokableFactory::class,
            ],
            'aliases' => [
                'structure' => StructureTemplateVariable::class,
            ],
            'shared' => [
                // Pas de pattern singleton pour StructureTemplateVariable car on doit pouvoir
                // disposer d'une instance par type de structure concrète : étab, ED, UR.
                StructureTemplateVariable::class => false,
            ]
        ],
    ]
];