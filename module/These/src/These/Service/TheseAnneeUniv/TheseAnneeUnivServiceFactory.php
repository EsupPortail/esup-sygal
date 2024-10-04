<?php

namespace These\Service\TheseAnneeUniv;

use Application\Service\Source\SourceService;
use Interop\Container\Containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TheseAnneeUnivServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $sourceService = $container->get(SourceService::class);

        $service = new TheseAnneeUnivService();
        $service->setSourceService($sourceService);

        return $service;
    }
}