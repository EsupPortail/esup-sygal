<?php

namespace Formation;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Soutenance\Renderer\SoutenanceMembreTemplateVariable;
use Soutenance\Renderer\SoutenancePropositionTemplateVariable;

return [
    'renderer' => [
        'template_variables' => [
            'factories' => [
                SoutenanceMembreTemplateVariable::class => InvokableFactory::class,
                SoutenancePropositionTemplateVariable::class => InvokableFactory::class,
            ],
            'aliases' => [
                'soutenanceMembre' => SoutenanceMembreTemplateVariable::class,
                'soutenanceProposition' => SoutenancePropositionTemplateVariable::class,
            ],
        ],
    ]
];