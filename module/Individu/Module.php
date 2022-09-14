<?php

namespace Individu;

use Laminas\Config\Factory as ConfigFactory;

class Module
{
    public function getConfig()
    {
        return ConfigFactory::fromFiles([
            __DIR__ . '/config/module.config.php',
            __DIR__ . '/config/individu.config.php',
            __DIR__ . '/config/individu-compl.config.php',
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
