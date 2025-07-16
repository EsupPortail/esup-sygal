<?php

namespace Application;

use Feature\PluginManager\FeaturePluginManager;
use Feature\PluginManager\FeaturePluginManagerFactory;

return [
    'service_manager' => [
        'factories' => [
            FeaturePluginManager::class => FeaturePluginManagerFactory::class,
        ],
    ],
];