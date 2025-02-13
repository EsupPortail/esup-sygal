<?php

use Candidat\Renderer\CandidatTemplateVariable;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'renderer' => [
        'template_variables' => [
            'factories' => [
                CandidatTemplateVariable::class => InvokableFactory::class,
            ],
            'aliases' => [
                'candidat' => CandidatTemplateVariable::class,
            ],
        ],
    ]
];