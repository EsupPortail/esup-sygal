<?php

namespace Formation\Form\Formation;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Structure\Service\Etablissement\EtablissementService;
use Individu\Service\IndividuService;
use Structure\Service\Structure\StructureService;
use Formation\Service\Module\ModuleService;
use Interop\Container\ContainerInterface;

class FormationHydratorFactory {

    /**
     * @param ContainerInterface $container
     * @return FormationHydrator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
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