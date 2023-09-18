<?php

namespace Substitution;

use Laminas\Config\Config;
use Laminas\Config\Factory as ConfigFactory;

class Module
{
    public function getConfig(): Config|array
    {
        return ConfigFactory::fromFiles([
            __DIR__ . '/config/module.config.php',
        ]);
    }

    public function getAutoloaderConfig(): array
    {
        return [
            'Laminas\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }
}
