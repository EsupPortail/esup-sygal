<?php

namespace Api;

use Laminas\ApiTools\Provider\ApiToolsProviderInterface;
use Laminas\Config\Factory as ConfigFactory;
use Laminas\Stdlib\Glob;

class Module implements ApiToolsProviderInterface
{
    public function getConfig(): array
    {
        $paths = array_merge(
            [__DIR__ . '/config/module.config.php'],
            //Glob::glob(__DIR__ . '/config/others/{,*.}{config}.php', Glob::GLOB_BRACE)
        );

        return ConfigFactory::fromFiles($paths);
    }

    public function getAutoloaderConfig(): array
    {
        return array(
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}