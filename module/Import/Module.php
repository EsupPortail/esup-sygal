<?php

namespace Import;

use Zend\Config\Factory as ConfigFactory;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

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
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConsoleUsage(Console $console)
    {
        return [
            // command
            'import --service=  --etablissement= [--source-code=]' => "Importer toutes les données d'un service d'un établissement.",
            // parameters
            ['--service',       "Requis. Identifiant du service, ex: 'variable'"],
            ['--etablissement', "Requis. Identifiant de l'établissement, ex: 'UCN'"],
            ['--source-code',   "Facultatif. Source code du seul enregistrement à importer"],

            // command
            'import-all --etablissement=' => "Importer toutes les données de tous les serviceq d'un établissement.",
            // parameters
            // parameters
            ['--etablissement', "Requis. Identifiant de l'établissement, ex: 'UCN'"],
        ];
    }
}
