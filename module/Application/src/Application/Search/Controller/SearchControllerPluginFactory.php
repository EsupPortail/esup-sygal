<?php

namespace Application\Search\Controller;

use Interop\Container\ContainerInterface;

class SearchControllerPluginFactory
{
    /**
     * @param ContainerInterface $container
     * @return SearchControllerPlugin
     */
    public function __invoke(ContainerInterface $container)
    {
        $plugin = new SearchControllerPlugin();

        return $plugin;
    }
}