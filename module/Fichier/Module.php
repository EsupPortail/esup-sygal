<?php

namespace Fichier;

use Laminas\Config\Factory as ConfigFactory;
use Laminas\Console\Adapter\AdapterInterface;
use Laminas\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Laminas\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Laminas\Stdlib\Glob;

class Module implements ConsoleBannerProviderInterface, ConsoleUsageProviderInterface
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
        return array(
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * @inheritDoc
     */
    public function getConsoleBanner(AdapterInterface $console): ?string
    {
        return "StepStar Module";
    }

    /**
     * @inheritDoc
     */
    public function getConsoleUsage(AdapterInterface $console)
    {
        return [
            /**
             * @see ConsoleController::migrerFichiersAction()
             */
            'fichier:migrer-fichiers --from=<from> --to=<to>' => "Migre les fichiers d'un storage (adapter) à un autre.",
            ['<from>', "Nom du storage adapter (service) source.", "Obligatoire"],
            ['<to>', "Nom du storage adapter (service) destination.", "Obligatoire"],
        ];
    }
}
