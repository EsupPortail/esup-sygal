<?php

namespace InscriptionAdministrative;

use Laminas\Config\Factory as ConfigFactory;
use Laminas\Stdlib\Glob;

class Module
{
    // Nom du module
    const NAME = __NAMESPACE__;

    public function getConfig(): array
    {
        $paths = array_merge(
            [__DIR__ . '/config/module.config.php'],
            Glob::glob(__DIR__ . '/config/others/{,*.}{config}.php', Glob::GLOB_BRACE)
        );

        return ConfigFactory::fromFiles($paths);
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
