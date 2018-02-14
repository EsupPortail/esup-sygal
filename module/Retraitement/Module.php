<?php

namespace Retraitement;

use Application\Event\UserAuthenticatedEventListener;
use Application\Event\UserRoleSelectedEventListener;
use Zend\Http\Request as HttpRequest;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\Glob;
use Zend\Config\Factory as ConfigFactory;
use Zend\Console\Adapter\AdapterInterface as Console;

class Module implements ConsoleUsageProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $application->getServiceManager()->get('translator');
        $eventManager = $application->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);


//        $sm = $application->getServiceManager();
//
//        /** @var RouteMatchInjector $routeMatchInjector */
//        $routeMatchInjector = $sm->get('RouteMatchInjector');
//        $eventManager->attachAggregate($routeMatchInjector);
//
//        /** @var UserAuthenticatedEventListener $listener */
//        $listener = $sm->get('UserAuthenticatedEventListener');
//        $listener->attach($eventManager);
//
//        /** @var UserRoleSelectedEventListener $listener */
//        $listener = $sm->get('UserRoleSelectedEventListener');
//        $listener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }



    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
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
