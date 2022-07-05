<?php

namespace Formation\Form\SessionStructureComplementaire;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Structure\StructureService;

class SessionStructureComplementaireHydratorFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionStructureComplementaireHydrator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container) : SessionStructureComplementaireHydrator
    {
        /**
         * @var StructureService $structureService
         */
        $structureService = $container->get(StructureService::class);

        $hydrator = new SessionStructureComplementaireHydrator();
        $hydrator->setStructureService($structureService);
        return $hydrator;
    }
}