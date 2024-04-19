<?php

namespace Application\Service\ListeDiffusion\Url;

use Application\RouteMatch;
use Interop\Container\ContainerInterface;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Unicaen\Console\Console;
use Webmozart\Assert\Assert;

class UrlServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UrlService
    {
        $config = $container->get('Config');

        $token = $config['liste-diffusion']['sympa']['generate_include_route_token'] ?? null;
        Assert::notNull($token, "Vous devez renseigner un token dans la clé de config ['liste-diffusion']['sympa']['generate_include_route_token']");
        Assert::minLength($token, 64, 'Vous devez renseigner un token de longueur %2$s ou plus');

        $service = new UrlService($token);

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