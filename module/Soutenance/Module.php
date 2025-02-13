<?php
namespace Soutenance;

use Laminas\Config\Factory as ConfigFactory;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\Glob;
use Soutenance\Listener\SoutenanceDynamicControllerResolver;

class Module
{
    public function onBootstrap(MvcEvent $event)
    {
        $eventManager = $event->getApplication()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_ROUTE, new SoutenanceDynamicControllerResolver(), -100);
    }

    public function getConfig()
    {
        $paths = array_merge(
            [__DIR__ . '/config/module.config.php'],
            Glob::glob(__DIR__ . '/config/others/{,*.}{config}.php', Glob::GLOB_BRACE)
        );

        return ConfigFactory::fromFiles($paths);
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Laminas\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
}
