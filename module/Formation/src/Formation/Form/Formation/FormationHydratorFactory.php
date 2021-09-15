<?php

namespace Formation\Form\Formation;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuService;
use Application\Service\Structure\StructureService;
use Formation\Service\Module\ModuleService;
use Interop\Container\ContainerInterface;

class FormationHydratorFactory {

    /**
     * @param ContainerInterface $container
     * @return FormationHydrator
     */
    public function __invoke(ContainerInterface $container) : FormationHydrator
    {
        /**
         * @var EtablissementService $etablissementService
         * @var IndividuService $individuService
         * @var ModuleService $moduleService
         * @var StructureService $structureService
         */
        $etablissementService = $container->get(EtablissementService::class);
        $individuService = $container->get(IndividuService::class);
        $moduleService = $container->get(ModuleService::class);
        $structureService = $container->get(StructureService::class);

        $hydrator = new FormationHydrator();
        $hydrator->setEtablissementService($etablissementService);
        $hydrator->setIndividuService($individuService);
        $hydrator->setModuleService($moduleService);
        $hydrator->setStructureService($structureService);
        return $hydrator;
    }
}