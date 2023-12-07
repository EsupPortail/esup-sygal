<?php

namespace Substitution\Service\Substitution\Structure;

use Psr\Container\ContainerInterface;
use Structure\Service\Structure\StructureService;

class StructureSubstitutionServiceFactory
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): StructureSubstitutionService
    {
        $service = new StructureSubstitutionService();

        /** @var \Structure\Service\Structure\StructureService $entityService */
        $entityService = $container->get(StructureService::class);
        $service->setEntityService($entityService);

        return $service;
    }
}