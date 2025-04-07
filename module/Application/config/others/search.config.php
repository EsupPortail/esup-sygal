<?php

namespace Application;

use Application\Search\Filter\SearchFilterPluginManager;
use Application\Search\Filter\SearchFilterPluginManagerFactory;
use Application\Search\SearchServicePluginManager;
use Application\Search\SearchServicePluginManagerFactory;

return [
    'service_manager' => [
        'factories' => [
            SearchServicePluginManager::class => SearchServicePluginManagerFactory::class,
            SearchFilterPluginManager::class => SearchFilterPluginManagerFactory::class,
        ],
    ],
    'search_services' => [
        'factories' => [
            // Any module providing additional search services would use this config array.
        ],
    ],
    'search_filters' => [
        'factories' => [
            // Any module providing additional search filters would use this config array.
        ],
    ],
];