<?php

namespace Application\Service\Url;

use Application\RouteMatch;
use Zend\Console\Console;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UrlServiceFactory implements AbstractFactoryInterface
{
    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param                         $name
     * @param                         $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $requestedClass = $requestedName;
        $isInNamespace = substr($requestedClass, 0, strlen(__NAMESPACE__)) === __NAMESPACE__;

        return $isInNamespace;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param                         $name
     * @param                         $requestedName
     * @return UrlService
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $class = $requestedName;

        /** @var UrlService $service */
        $service = new $class();

        /** @var RouteStackInterface $router */
        $router = $serviceLocator->get(Console::isConsole() ? 'HttpRouter' : 'Router');
        $match = $serviceLocator->get('application')->getMvcEvent()->getRouteMatch();
        if ($match instanceof RouteMatch) {
            $service->setRouteMatch($match);
        }
        $service->setRouter($router);

        return $service;
    }
}