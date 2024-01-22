<?php

namespace Import\Controller\Factory;

use Application\EventRouterReplacer;
use Application\Service\Source\SourceService;
use Import\Controller\ImportObserverController;
use Import\Model\Service\ImportObservResultService;
use Interop\Container\ContainerInterface;
use Laminas\Router\Http\TreeRouteStack;
use These\Service\These\TheseService;
use UnicaenDbImport\Entity\Db\Service\ImportObserv\ImportObservService;

class ImportObserverControllerFactory
{
    /**
     * Create service
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ImportObserverController
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

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $controller->setSourceService($sourceService);

        return $controller;
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function getCliConfig(ContainerInterface $container): array
    {
        $config = $container->get('Config');

        return [
            'domain' => $config['cli_config']['domain'] ?? null,
            'scheme' => $config['cli_config']['scheme'] ?? null,
        ];
    }
}