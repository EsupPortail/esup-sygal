<?php

namespace Application\Controller\Factory;

use Application\Controller\TheseObserverController;
use Application\EventRouterReplacer;
use Application\Service\These\TheseObserverService;
use Interop\Container\ContainerInterface;
use Zend\Router\Http\TreeRouteStack;

class TheseObserverControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return TheseObserverController
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var TreeRouteStack $httpRouter */
        $httpRouter = $container->get('HttpRouter');
        $cliConfig = $this->getCliConfig($container);
        $routerReplacer = new EventRouterReplacer($httpRouter, $cliConfig);

        /** @var TheseObserverService $theseObserverService */
        $theseObserverService = $container->get('TheseObserverService');

        $controller = new TheseObserverController();
        $controller->setEventRouterReplacer($routerReplacer);
        $controller->setTheseObserverService($theseObserverService);

        return $controller;
    }

    /**
     * @param ContainerInterface $container
     * @return array
     */
    private function getCliConfig(ContainerInterface $container)
    {
        $config = $container->get('Config');

        return [
            'domain' => isset($config['cli_config']['domain']) ? $config['cli_config']['domain'] : null,
            'scheme' => isset($config['cli_config']['scheme']) ? $config['cli_config']['scheme'] : null,
        ];
    }
}