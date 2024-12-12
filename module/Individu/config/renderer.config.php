<?php

namespace Individu;

use Individu\Renderer\IndividuTemplateVariable;
use Laminas\ServiceManager\Factory\InvokableFactory;

return array(
    'renderer' => [
        'template_variables' => [
            'factories' => [
                IndividuTemplateVariable::class => InvokableFactory::class,
            ],
            'aliases' => [
                'individu' => IndividuTemplateVariable::class,
            ],
        ],
    ],
);
