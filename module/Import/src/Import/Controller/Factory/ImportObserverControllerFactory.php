<?php

namespace Import\Controller\Factory;

use Application\EventRouterReplacer;
use Application\Service\These\TheseService;
use Import\Controller\ImportObserverController;
use Import\Service\ImportObserv\ImportObservService;
use Import\Service\ImportObservEtabResult\ImportObservEtabResultService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportObserverControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return ImportObserverController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /** @var TreeRouteStack $httpRouter */
        $httpRouter = $sl->get('HttpRouter');
        $cliConfig = $this->getCliConfig($sl);

        $routerReplacer = new EventRouterReplacer($httpRouter, $cliConfig);

        /** @var ImportObservService $importObservService */
        $importObservService = $sl->get(ImportObservService::class);

        /** @var ImportObservEtabResultService $importObservEtabResultService */
        $importObservEtabResultService = $sl->get(ImportObservEtabResultService::class);

        /** @var TheseService $theseService */
        $theseService = $sl->get('TheseService');

        $controller = new ImportObserverController();
        $controller->setEventRouterReplacer($routerReplacer);
        $controller->setImportObservService($importObservService);
        $controller->setImportObservEtabResultService($importObservEtabResultService);
        $controller->setTheseService($theseService);

        return $controller;
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