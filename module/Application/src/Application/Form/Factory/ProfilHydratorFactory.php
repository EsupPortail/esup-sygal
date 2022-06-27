<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\ProfilHydrator;
use Structure\Service\Structure\StructureService;
use Interop\Container\ContainerInterface;

class ProfilHydratorFactory {

    public function __invoke(ContainerInterface $container)
    {
        /** @var StructureService $structureService */
        $structureService = $container->get(StructureService::class);

        /** @var ProfilHydrator $hydrator */
        $hydrator = new ProfilHydrator();
        $hydrator->setStructureService($structureService);
        return $hydrator;
    }


}