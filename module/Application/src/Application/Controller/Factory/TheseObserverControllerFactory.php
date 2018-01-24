<?php

namespace Application\Controller\Factory;

use Application\Controller\TheseObserverController;
use Application\EventRouterReplacer;
use Application\Service\These\TheseObserverService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\ServiceManager\ServiceLocatorInterface;

class TheseObserverControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return TheseObserverController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /** @var TreeRouteStack $httpRouter */
        $httpRouter = $sl->get('HttpRouter');
        $cliConfig = $this->getCliConfig($sl);

        $routerReplacer = new EventRouterReplacer($httpRouter, $cliConfig);

        $controller = new TheseObserverController();
        $controller->setEventRouterReplacer($routerReplacer);
        $controller->setTheseObserverService($this->getTheseObserverService($sl));

        return $controller;
    }

    /**
     * @param ServiceLocatorInterface $sl
     * @return TheseObserverService
     */
    private function getTheseObserverService(ServiceLocatorInterface $sl)
    {
        /** @var TheseObserverService $service */
        $service = $sl->get('TheseObserverService');

        return $service;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return array
     */
    private function getCliConfig(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');

        return [
            'domain' => isset($config['cli_config']['domain']) ? $config['cli_config']['domain'] : null,
            'scheme' => isset($config['cli_config']['scheme']) ? $config['cli_config']['scheme'] : null,
        ];
    }
}