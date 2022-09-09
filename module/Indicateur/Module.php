<?php
namespace Indicateur;

use Laminas\Config\Factory as ConfigFactory;

class Module
{
    public function getConfig()
    {
        $paths = array_merge(
            [__DIR__ . '/config/module.config.php']
        );
        return ConfigFactory::fromFiles($paths);
    }

    public function getAutoloaderConfig()
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
