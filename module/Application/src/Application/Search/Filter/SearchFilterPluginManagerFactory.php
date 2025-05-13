<?php

namespace Application\Search\Filter;

use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SearchFilterPluginManagerFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SearchFilterPluginManager
    {
        $pluginManager = new SearchFilterPluginManager($container, $options ?: []);

        // If we do not have a config service, nothing more to do
        if (! $container->has('config')) {
            return $pluginManager;
        }

        $config = $container->get('config');

        // If we do not have validators configuration, nothing more to do
        if (! isset($config['search_filters']) || ! is_array($config['search_filters'])) {
            return $pluginManager;
        }

        // Wire service configuration for search filters
        (new Config($config['search_filters']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }
}