<?php

namespace Depot\Service\Url;

use Application\RouteMatch;
use Interop\Container\ContainerInterface;
use Unicaen\Console\Console;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class UrlDepotServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UrlDepotService
    {
        $service = new UrlDepotService();

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