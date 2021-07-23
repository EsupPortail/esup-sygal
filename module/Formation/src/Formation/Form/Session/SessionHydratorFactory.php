<?php

namespace Formation\Form\Session;

use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuService;
use Application\Service\Structure\StructureService;
use Interop\Container\ContainerInterface;

class SessionHydratorFactory {

    /**
     * @param ContainerInterface $container
     * @return SessionHydrator
     */
    public function __invoke(ContainerInterface $container) : SessionHydrator
    {
        /**
         * @var EtablissementService $etablissementService
         * @var IndividuService $individuService
         * @var StructureService $structureService
         */
        $etablissementService = $container->get(EtablissementService::class);
        $individuService = $container->get(IndividuService::class);
        $structureService = $container->get(StructureService::class);

        $hydrator = new SessionHydrator();
        $hydrator->setEtablissementService($etablissementService);
        $hydrator->setIndividuService($individuService);
        $hydrator->setStructureService($structureService);
        return $hydrator;
    }
}