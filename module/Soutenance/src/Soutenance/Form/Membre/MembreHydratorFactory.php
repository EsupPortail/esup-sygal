<?php

namespace Soutenance\Form\Membre;

use Soutenance\Service\Membre\MembreService;
use Zend\Stdlib\Hydrator\HydratorPluginManager;


class MembreHydratorFactory
{
    public function __invoke(HydratorPluginManager $hydratorPluginManager)
    {
        $servicelocator = $hydratorPluginManager->getServiceLocator();
        /** @var MembreService $membreService */
        $membreService = $servicelocator->get(MembreService::class);

        /** @var MembreForm $form */
        $hydrator = new MembreHydrator();
        $hydrator->setMembreService($membreService);

        return $hydrator;
    }
}