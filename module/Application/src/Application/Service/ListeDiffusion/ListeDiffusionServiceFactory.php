<?php

namespace Application\Service\ListeDiffusion;

use Application\Service\Individu\IndividuService;
use Application\Service\ListeDiffusion\Handler\ListeDiffusionHandler;
use Interop\Container\ContainerInterface;

class ListeDiffusionServiceFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $service = new ListeDiffusionService();

        $config = $container->get('Config');
        $service->setConfig($config['liste-diffusion'] ?? []);

        /**
         * @var IndividuService $individuService
         */
        $individuService = $container->get('IndividuService');
        $service->setIndividuService($individuService);
        $service->setAvailableHandlers([
            $container->get(ListeDiffusionHandler::class),
        ]);

        return $service;
    }
}