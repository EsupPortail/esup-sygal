<?php
namespace Doctorant;

use Doctorant\Renderer\DoctorantTemplateVariable;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'renderer' => [
        'template_variables' => [
            'factories' => [
                DoctorantTemplateVariable::class => InvokableFactory::class,
            ],
            'aliases' => [
                'doctorant' => DoctorantTemplateVariable::class,
            ],
        ],
    ]
];