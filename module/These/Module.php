<?php

namespace These;

use Laminas\Config\Factory as ConfigFactory;
use Laminas\Console\Adapter\AdapterInterface as Console;
use Laminas\Stdlib\Glob;

class Module
{
    public function getConfig()
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

    public function getConsoleUsage(Console $console): array
    {
        return [
            // Describe available commands
            'transfer-these-data --source-id= --destination-id=' =>
                "Transférer toutes les données saisies sur une thèse *historisée* vers une autre thèse",

            // Describe expected parameters
            ['--source-id',       "Id de la thèse historisée source"],
            ['--destination-id',  "Id de la thèse destination"],
        ];
    }
}
