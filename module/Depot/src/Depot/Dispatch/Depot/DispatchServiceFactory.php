<?php

namespace Depot\Dispatch\Depot;

use Application\RouteMatch;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Unicaen\Console\Console;

class DispatchServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DispatchService
    {
        $service = new DispatchService();

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