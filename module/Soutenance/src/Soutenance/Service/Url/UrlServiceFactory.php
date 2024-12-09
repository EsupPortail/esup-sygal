<?php

namespace Soutenance\Service\Url;

use Application\RouteMatch;
use Interop\Container\ContainerInterface;
use Laminas\Router\RouteStackInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Soutenance\Service\Membre\MembreService;
use Unicaen\Console\Console;

class UrlServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return UrlService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : UrlService
    {
        $service = new UrlService();

        /** @var MembreService $membreService */
        $membreService = $container->get(MembreService::class);
        $service->setMembreService($membreService);

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