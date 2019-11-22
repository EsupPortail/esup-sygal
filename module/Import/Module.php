<?php

namespace Import;

use Application\Entity\Db\ImportObserv;
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
        return ConfigFactory::fromFiles([
            __DIR__ . '/config/synchro.config.php',
            __DIR__ . '/config/import.config.php',
        ]);
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
            'import --service=  --etablissement= [--source-code=] [--synchronize=] [--verbose] [--em=]' => "Importer toutes les données d'un service d'un établissement.",
            // parameters
            ['--service',       "Requis. Identifiant du service, ex: 'variable'"],
            ['--etablissement', "Requis. Identifiant de l'établissement, ex: 'UCN'"],
            ['--source-code',   "Facultatif. Source code du seul enregistrement à importer"],
            ['--synchronize',   "Facultatif. Réaliser ou non la synchro SRC_XXX => XXX. Valeurs possibles: 0, 1. Valeur par défaut: 1."],
            ['--verbose',       "Facultatif. Activer les logs verbeux (debug)."],
            ['--em',            "Facultatif. Nom de l'EntityManager à utiliser. Valeur par défaut: 'orm_default'."],

            // command
            'import-all --etablissement= [--synchronize=] [--verbose] [--em=]' => "Importer toutes les données de tous les serviceq d'un établissement.",
            // parameters
            ['--etablissement',          "Requis. Identifiant de l'établissement, ex: 'UCN'"],
            ['--breakOnServiceNotFound', "Facultatif. Faut-il stopper si un service appelé n'existe pas. Valeurs possibles: 0, 1. Valeur par défaut: 1."],
            ['--synchronize',            "Facultatif. Réaliser ou non la synchro SRC_XXX => XXX. Valeurs possibles: 0, 1. Valeur par défaut: 1."],
            ['--verbose',                "Facultatif. Activer les logs verbeux (debug)."],
            ['--em',                     "Facultatif. Nom de l'EntityManager à utiliser. Valeur par défaut: 'orm_default'."],

            // command
            'update-these --id= [--verbose] [--em=]' => "Mettre à jour une thèse et ses données liées.",
            // parameters
            ['--id',      "Requis. Id de la thèse"],
            ['--verbose', "Facultatif. Activer les logs verbeux (debug)."],
            ['--em',      "Facultatif. Nom de l'EntityManager à utiliser. Valeur par défaut: 'orm_default'."],

            // command
            'synchronize --service= [--em=]' => "Lancer la synchro UnicaenImport pour un seul service.",
            // parameters
            ['--service', "Requis. Identifiant du service, ex: 'variable'"],
            ['--em',      "Facultatif. Nom de l'EntityManager à utiliser. Valeur par défaut: 'orm_default'."],

            // command
            'synchronize-all [--em=]' => "Lancer la synchro UnicaenImport pour tous les services.",
            // parameters
            ['--em', "Facultatif. Nom de l'EntityManager à utiliser. Valeur par défaut: 'orm_default'."],

            // command
            'process-observed-import-results --etablissement= [--import-observ=] [--source-code=]' => "Traitement des résultats d'observation de certains changements durant la synchro.",
            // parameters
            ['--etablissement', "Requis. Identifiant de l'établissement, ex: 'UCN'"],
            ['--import-observ', "Facultatif. Code de la seule observation voulue. Valeurs possibles: " . implode(', ', ImportObserv::CODES)],
            ['--source-code',   "Facultatif. Source code de la seule thèse à prendre en compte."],
        ];
    }
}
