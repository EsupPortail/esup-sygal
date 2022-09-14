<?php

namespace Doctorant;

use Laminas\Config\Factory as ConfigFactory;

class Module
{
    public function getConfig()
    {
        return ConfigFactory::fromFile(__DIR__ . '/config/module.config.php');
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
