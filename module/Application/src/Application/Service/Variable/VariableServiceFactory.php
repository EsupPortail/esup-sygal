<?php

namespace Application\Service\Variable;

use Application\Service\Source\SourceService;
use Interop\Container\ContainerInterface;

class VariableServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return VariableService
     */
    public function __invoke(ContainerInterface $container)
    {
        $service = new VariableService();

        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);
        $service->setSourceService($sourceService);

        return $service;
    }
}
