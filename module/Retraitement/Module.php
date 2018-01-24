<?php

namespace Retraitement;

use Application\Event\UserAuthenticatedEventListener;
use Application\Event\UserRoleSelectedEventListener;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\Glob;
use Zend\Config\Factory as ConfigFactory;

class Module
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
}
