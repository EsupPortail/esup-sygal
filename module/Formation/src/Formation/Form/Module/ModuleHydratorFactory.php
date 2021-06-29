<?php

namespace Formation\Form\Module;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuService;
use Application\Service\Structure\StructureService;
use Interop\Container\ContainerInterface;

class ModuleHydratorFactory {

    /**
     * @param ContainerInterface $container
     * @return ModuleHydrator
     */
    public function __invoke(ContainerInterface $container) : ModuleHydrator
    {
        /**
         * @var EtablissementService $etablissementService
         * @var IndividuService $individuService
         * @var StructureService $structureService
         */
        $etablissementService = $container->get(EtablissementService::class);
        $individuService = $container->get(IndividuService::class);
        $structureService = $container->get(StructureService::class);

        $hydrator = new ModuleHydrator();
        $hydrator->setEtablissementService($etablissementService);
        $hydrator->setIndividuService($individuService);
        $hydrator->setStructureService($structureService);
        return $hydrator;
    }
}