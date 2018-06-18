<?php

namespace Application\Service\Structure;

use Application\Service\Source\SourceService;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

/**
 * @author Unicaen
 */
class StructureServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return StructureService
     */
    public function __invoke(ContainerInterface $container, $requestedName, $options = null)
    {
        /** @var SourceService $sourceService */
        $sourceService = $container->get(SourceService::class);

        $service = new StructureService;
        $service->setSourceService($sourceService);

        return $service;
    }
}