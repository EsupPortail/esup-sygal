<?php

namespace Application\Search;

use Application\Search\Filter\SearchFilterPluginManager;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class SearchServicePluginManagerFactory implements FactoryInterface
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SearchServicePluginManager
    {
        $pluginManager = new SearchServicePluginManager($container, $options ?: []);

        /** @var SearchFilterPluginManager $searchFilterPluginManager */
        $searchFilterPluginManager = $container->get(SearchFilterPluginManager::class);
        $pluginManager->setSearchFilterPluginManager($searchFilterPluginManager);

        // If we do not have a config service, nothing more to do
        if (! $container->has('config')) {
            return $pluginManager;
        }

        $config = $container->get('config');

        // If we do not have validators configuration, nothing more to do
        if (! isset($config['search_services']) || ! is_array($config['search_services'])) {
            return $pluginManager;
        }

        // Wire service configuration for search services
        (new Config($config['search_services']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }
}