<?php

namespace StepStar;

use Zend\Config\Factory as ConfigFactory;
use Zend\Console\Adapter\AdapterInterface;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\Glob;

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
        $paths = array_merge(
            [__DIR__ . '/config/module.config.php'],
            Glob::glob(__DIR__ . '/config/others/{,*.}{config}.php', Glob::GLOB_BRACE)
        );

        return ConfigFactory::fromFiles($paths);
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
            'step-star generer-xml --these=<id> --to=<to>' => "Génère le fichier XML intermédiaire d'une thèse.",
            [ '<id>', "Id de la thèse concernée.", "Obligatoire"],
            [ '<to>', "Chemin du fichier XML à produire.", "Obligatoire"],

            /**
             * @see ConsoleController::genererTefAction()
             */
            'step-star generer-tef --from=<from> [--dir=<dir>]' => "Génère un fichier TEF en transformant un fichier XML intermédiaire.",
            [ '<from>', "Chemin complet du fichier XML intermédiaire à transformer contenant les thèses", "Obligatoire"],
            [ '<dir>', "Répertoire destination.", "Facultatif"],

            /**
             * @see ConsoleController::deposerAction()
             */
            'step-star deposer --tef=<tef> [--zip=<zip>]' => "Exporte vers STAR un fichier TEF et éventuellement un fichier ZIP.",
            [ '<tef>', "Chemin complet du fichier TEF", "Obligatoire"],
            [ '<zip>', "Chemin complet du fichier ZIP éventuel.", "Facultatif"],
        ];
    }
}
