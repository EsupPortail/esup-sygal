<?php

namespace Import\Controller\Factory;

use Application\EventRouterReplacer;
use Application\Service\These\TheseService;
use Import\Controller\ImportObserverController;
use Import\Service\ImportObserv\ImportObservService;
use Import\Service\ImportObservEtabResult\ImportObservEtabResultService;
use Interop\Container\ContainerInterface;
use Zend\Router\Http\TreeRouteStack;

class ImportObserverControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return ImportObserverController
     */
    public function __invoke(ContainerInterface $container)
    {
        /** @var TreeRouteStack $httpRouter */
        $httpRouter = $container->get('HttpRouter');
        $cliConfig = $this->getCliConfig($container);

        $routerReplacer = new EventRouterReplacer($httpRouter, $cliConfig);

        /** @var ImportObservService $importObservService */
        $importObservService = $container->get(ImportObservService::class);

        /** @var ImportObservEtabResultService $importObservEtabResultService */
        $importObservEtabResultService = $container->get(ImportObservEtabResultService::class);

        /** @var TheseService $theseService */
        $theseService = $container->get('TheseService');

        $controller = new ImportObserverController();
        $controller->setEventRouterReplacer($routerReplacer);
        $controller->setImportObservService($importObservService);
        $controller->setImportObservEtabResultService($importObservEtabResultService);
        $controller->setTheseService($theseService);

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