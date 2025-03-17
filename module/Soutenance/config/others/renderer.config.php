<?php

namespace Soutenance;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Soutenance\Renderer\SoutenanceActeurTemplateVariable;
use Soutenance\Renderer\SoutenanceMembreTemplateVariable;
use Soutenance\Renderer\SoutenancePropositionTemplateVariable;

return [
    'renderer' => [
        'template_variables' => [
            'factories' => [
                SoutenanceMembreTemplateVariable::class => InvokableFactory::class,
                SoutenanceActeurTemplateVariable::class => InvokableFactory::class,
                SoutenancePropositionTemplateVariable::class => InvokableFactory::class,
            ],
            'aliases' => [
                'soutenanceMembre' => SoutenanceMembreTemplateVariable::class,
                'soutenanceActeur' => SoutenanceActeurTemplateVariable::class,
                'soutenanceProposition' => SoutenancePropositionTemplateVariable::class,
            ],
        ],
    ]
];