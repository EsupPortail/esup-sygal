<?php

namespace Application\Service\Url;

use Application\RouteMatch;
use Interop\Container\ContainerInterface;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Unicaen\Console\Console;

class UrlServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UrlService
    {
        $service = new UrlService();

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