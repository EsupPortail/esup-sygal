<?php

namespace Retraitement;

use Laminas\Console\Adapter\AdapterInterface as Console;
use Laminas\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;

class Module implements ConsoleUsageProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $application->getServiceManager()->get('translator');
        $eventManager = $application->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }



    public function getAutoloaderConfig()
    {
        return [
            'Laminas\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    public function getConsoleUsage(Console $console)
    {
        return [
            // Describe available commands
            'fichier retraiter [--tester-archivabilite] [--notifier=] FICHIER' =>
                'Créer un fichier retraité et tester éventuellement son archivabilité.',

            // Describe expected parameters
            ['FICHIER',                "Id du fichier à retraiter"],
            ['--tester-archivabilite', "(facultatif) Tester l'archivabilité du fichier retraité"],
            ['--notifier',             "(facultatif) Adresses électroniques auxquelles envoyer un courriel une fois le retraitement terminé"],
        ];
    }
}
