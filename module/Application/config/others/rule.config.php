<?php

namespace Application;

use Application\Rule\PluginManager\RulePluginManager;
use Application\Rule\PluginManager\RulePluginManagerFactory;

return [
    'service_manager' => [
        'factories' => [
            RulePluginManager::class => RulePluginManagerFactory::class,
        ],
    ],
];