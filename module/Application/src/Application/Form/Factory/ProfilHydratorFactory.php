<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\ProfilHydrator;
use Application\Service\Structure\StructureService;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class ProfilHydratorFactory {

    public function __invoke (HydratorPluginManager $serviceLocator)
    {
        /** @var StructureService $structureService */
        $structureService = $serviceLocator->getServiceLocator()->get(StructureService::class);

        /** @var ProfilHydrator $hydrator */
        $hydrator = new ProfilHydrator();
        $hydrator->setStructureService($structureService);
        return $hydrator;
    }


}