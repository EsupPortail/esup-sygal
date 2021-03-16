<?php

namespace StepStar;

use Zend\Console\Adapter\AdapterInterface;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module implements ConsoleBannerProviderInterface, ConsoleUsageProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig(): array
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
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
             * @see ConsoleController::genererXmlAction()
             */
            'step-star generer-xml --these=<id> --to=<to> [--anonymize]' => "Génère le fichier XML intermédiaire d'une thèse.",
            [ '<id>', "Id de la thèse concernée.", "Obligatoire"],
            [ '<to>', "Chemin du fichier XML à produire.", "Obligatoire"],
            [ '--anonymize', "Active l'anonymisation des données.", "Facultatif"],

            /**
             * @see ConsoleController::genererTefAction()
             */
            'step-star generer-tef --from=<from> [--dir=<dir>]' => "Génère un fichier TEF en transformant un fichier XML intermédiaire.",
            [ '<from>', "Chemin complet du fichier XML intermédiaire à transformer contenant les thèses", "Obligatoire"],
            [ '<dir>', "Répertoire destination.", "Facultatif"],

            /**
             * @see ConsoleController::genererZipAction()
             */
            'step-star generer-zip --these=<id>' => "Génère l'archive Zip contenant les fichiers d'une thèse.",
            [ '<id>', "Id de la thèse concernée.", "Obligatoire"],

            /**
             * @see ConsoleController::deposerAction()
             */
            'step-star deposer --tef=<tef> [--zip=<zip>]' => "Exporte vers STAR un fichier TEF et éventuellement un fichier ZIP.",
            [ '<tef>', "Chemin complet du fichier TEF", "Obligatoire"],
            [ '<zip>', "Chemin complet du fichier ZIP éventuel.", "Facultatif"],
        ];
    }
}
