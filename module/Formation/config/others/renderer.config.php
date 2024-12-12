<?php

namespace Formation;

use Formation\Renderer\FormationInscriptionTemplateVariable;
use Formation\Renderer\FormationSessionTemplateVariable;
use Formation\Renderer\FormationTemplateVariable;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'renderer' => [
        'template_variables' => [
            'factories' => [
                FormationTemplateVariable::class => InvokableFactory::class,
                FormationInscriptionTemplateVariable::class => InvokableFactory::class,
                FormationSessionTemplateVariable::class => InvokableFactory::class,
            ],
            'aliases' => [
                'formation' => FormationTemplateVariable::class,
                'formationInscription' => FormationInscriptionTemplateVariable::class,
                'formationSession' => FormationSessionTemplateVariable::class,
            ],
        ],
    ]
];