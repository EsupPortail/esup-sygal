<?php

namespace Depot\Controller\Factory;

use Application\EventRouterReplacer;
use Depot\Controller\ObserverController;
use Depot\Service\These\TheseObserverService;
use Psr\Container\ContainerInterface;
use Laminas\Router\Http\TreeRouteStack;

class ObserverControllerFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ObserverController
    {
        /** @var TreeRouteStack $httpRouter */
        $httpRouter = $container->get('HttpRouter');
        $cliConfig = $this->getCliConfig($container);
        $routerReplacer = new EventRouterReplacer($httpRouter, $cliConfig);

        /** @var TheseObserverService $theseObserverService */
        $theseObserverService = $container->get(TheseObserverService::class);

        $controller = new ObserverController();
        $controller->setEventRouterReplacer($routerReplacer);
        $controller->setTheseObserverService($theseObserverService);

        return $controller;
    }

    private function getCliConfig(ContainerInterface $container): array
    {
        $config = $container->get('Config');

        return [
            'domain' => $config['cli_config']['domain'] ?? null,
            'scheme' => $config['cli_config']['scheme'] ?? null,
        ];
    }
}