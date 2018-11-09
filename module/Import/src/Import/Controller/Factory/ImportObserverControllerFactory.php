<?php

namespace Import\Controller\Factory;

use Application\EventRouterReplacer;
use Import\Controller\ImportObserverController;
use Import\Service\ImportObserv\ImportObservService;
use Import\Service\ImportObservResult\ImportObservResultService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportObserverControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return \Import\Controller\ImportObserverController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /** @var TreeRouteStack $httpRouter */
        $httpRouter = $sl->get('HttpRouter');
        $cliConfig = $this->getCliConfig($sl);

        $routerReplacer = new EventRouterReplacer($httpRouter, $cliConfig);

        /** @var ImportObservService $importObservService */
        $importObservService = $sl->get('ImportObservService');

        $controller = new ImportObserverController();
        $controller->setImportObservService($importObservService);
        $controller->setEventRouterReplacer($routerReplacer);
        $controller->setImportObservResultService($this->getImportObservResultService($sl));

        return $controller;
    }

    /**
     * @param ServiceLocatorInterface $sl
     * @return ImportObservResultService
     */
    private function getImportObservResultService(ServiceLocatorInterface $sl)
    {
        /** @var \Import\Service\ImportObservResult\ImportObservResultService $service */
        $service = $sl->get('ImportObservResultService');

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