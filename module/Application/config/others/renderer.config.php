<?php

namespace Application;

use Application\Renderer\RoleTemplateVariable;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManager;
use Application\Renderer\Template\Variable\PluginManager\TemplateVariablePluginManagerFactory;
use Application\Renderer\ValidationTemplateVariable;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'renderer' => [
        'template_variables' => [
            'factories' => [
                RoleTemplateVariable::class => InvokableFactory::class,
                ValidationTemplateVariable::class => InvokableFactory::class,
            ],
            'aliases' => [
                'role' => RoleTemplateVariable::class,
                'validation' => ValidationTemplateVariable::class,
            ]
        ],
    ],
    'service_manager' => [
        'factories' => [
            TemplateVariablePluginManager::class => TemplateVariablePluginManagerFactory::class,
        ],
    ],
];