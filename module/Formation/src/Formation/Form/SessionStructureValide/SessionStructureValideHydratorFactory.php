<?php

namespace Formation\Form\SessionStructureValide;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Structure\StructureService;

class SessionStructureValideHydratorFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionStructureValideHydrator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SessionStructureValideHydrator
    {
        /**
         * @var StructureService $structureService
         */
        $structureService = $container->get(StructureService::class);

        $hydrator = new SessionStructureValideHydrator();
        $hydrator->setStructureService($structureService);
        return $hydrator;
    }
}