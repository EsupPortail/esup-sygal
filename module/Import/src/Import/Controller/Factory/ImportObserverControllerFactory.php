<?php

namespace Import\Controller\Factory;

use Application\EventRouterReplacer;
use Application\Service\These\TheseService;
use Import\Controller\ImportObserverController;
use Import\Model\Service\ImportObservResultService;
use Interop\Container\ContainerInterface;
use UnicaenDbImport\Entity\Db\Service\ImportObserv\ImportObservService;
use Laminas\Router\Http\TreeRouteStack;

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

        /** @var \Import\Model\Service\ImportObservResultService $importObservResultEtabService */
        $importObservResultEtabService = $container->get(ImportObservResultService::class);

        /** @var TheseService $theseService */
        $theseService = $container->get('TheseService');

        $controller = new ImportObserverController();
        $controller->setEventRouterReplacer($routerReplacer);
        $controller->setImportObservService($importObservService);
        $controller->setImportObservResultService($importObservResultEtabService);
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