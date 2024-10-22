<?php

namespace Application\Service\ListeDiffusion;

use Application\Service\ListeDiffusion\Handler\ListeDiffusionHandler;
use Application\Service\ListeDiffusion\Url\UrlService;
use Individu\Service\IndividuService;
use Interop\Container\ContainerInterface;

class ListeDiffusionServiceFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ListeDiffusionService
    {
        $service = new ListeDiffusionService();

        $config = $container->get('Config');
        $service->setConfig($config['liste-diffusion'] ?? []);

        /**
         * @var IndividuService $individuService
         */
        $individuService = $container->get(IndividuService::class);
        $service->setIndividuService($individuService);
        $service->setAvailableHandlers([
            $container->get(ListeDiffusionHandler::class),
        ]);

        /**
         * @var \Application\Service\ListeDiffusion\Url\UrlService $urlService
         */
//        $urlService = $container->get(UrlService::class);
//        $service->setUrlService($urlService);

        return $service;
    }
}