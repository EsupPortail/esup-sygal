<?php

namespace Application\Service\Structure;

use Application\Service\Source\SourceService;
use Import\Service\SynchroService;
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
        /**
         * @var SourceService $sourceService
         * @var SynchroService $synchroService
         */
        $sourceService = $container->get(SourceService::class);
        $synchroService = $container->get(SynchroService::class);

        $service = new StructureService;
        $service->setSourceService($sourceService);
        $service->setSynchroService($synchroService);

        return $service;
    }
}