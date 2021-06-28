<?php

namespace Formation\Form\Formation;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuService;
use Application\Service\Structure\StructureService;
use Application\Service\Utilisateur\UtilisateurService;
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
         * @var StructureService $structureService
         */
        $etablissementService = $container->get(EtablissementService::class);
        $individuService = $container->get(IndividuService::class);
        $structureService = $container->get(StructureService::class);

        $hydrator = new FormationHydrator();
        $hydrator->setEtablissementService($etablissementService);
        $hydrator->setIndividuService($individuService);
        $hydrator->setStructureService($structureService);
        return $hydrator;
    }
}