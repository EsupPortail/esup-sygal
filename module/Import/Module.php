<?php

namespace Import;

use Import\Model\ImportObserv;
use Laminas\Config\Factory as ConfigFactory;
use Unicaen\Console\Adapter\AdapterInterface as Console;
use Laminas\Mvc\ModuleRouteListener;
use Laminas\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return ConfigFactory::fromFiles([
            __DIR__ . '/config/synchro.config.php',
            __DIR__ . '/config/import.config.php',
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

    public function getConsoleUsage(Console $console): array
    {
        return [
            // command
            'update-these --id= [--verbose] [--em=]' => "Mettre à jour une thèse et ses données liées.",
            // parameters
            ['--id', "Requis. Id de la thèse"],
            ['--verbose', "Facultatif. Activer les logs verbeux (debug)."],
            ['--em', "Facultatif. Nom de l'EntityManager à utiliser. Valeur par défaut: 'orm_default'."],

            // command
            'process-observed-import-results --etablissement= [--import-observ=] [--source-code=]' => "Traitement des résultats d'observation de certains changements durant la synchro.",
            // parameters
            ['--etablissement', "Requis. Identifiant de l'établissement, ex: 'UCN'"],
            ['--import-observ', "Facultatif. Code de la seule observation voulue. Valeurs possibles: " . implode(', ', ImportObserv::CODES)],
            ['--source-code', "Facultatif. Source code de la seule thèse à prendre en compte."],
        ];
    }
}
