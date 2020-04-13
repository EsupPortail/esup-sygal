<?php

namespace Application\Service\Url;

use Application\RouteMatch;
use Interop\Container\ContainerInterface;
use Zend\Console\Console;
use Zend\Router\RouteStackInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class UrlServiceFactory implements AbstractFactoryInterface
{
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $requestedClass = $requestedName;

        return substr($requestedClass, 0, strlen(__NAMESPACE__)) === __NAMESPACE__;
    }

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $class = $requestedName;

        /** @var UrlService $service */
        $service = new $class();

        /** @var RouteStackInterface $router */
        $router = $container->get(Console::isConsole() ? 'HttpRouter' : 'Router');
        $match = $container->get('application')->getMvcEvent()->getRouteMatch();
        if ($match instanceof RouteMatch) {
            $service->setRouteMatch($match);
        }
        $service->setRouter($router);

        return $service;
    }
}