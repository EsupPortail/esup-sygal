<?php

namespace Soutenance\Form\SoutenanceMembre;

use Soutenance\Service\Membre\MembreService;
use Zend\Stdlib\Hydrator\HydratorPluginManager;


class SoutenanceMembreHydratorFactory
{
    public function __invoke(HydratorPluginManager $hydratorPluginManager)
    {
        $servicelocator = $hydratorPluginManager->getServiceLocator();
        /** @var MembreService $membreService */
        $membreService = $servicelocator->get(MembreService::class);

        /** @var SoutenanceMembreForm $form */
        $hydrator = new SoutenanceMembreHydrator();
        $hydrator->setMembreService($membreService);

        return $hydrator;
    }
}