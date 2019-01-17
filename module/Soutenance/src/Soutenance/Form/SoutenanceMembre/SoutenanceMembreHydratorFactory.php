<?php

namespace Soutenance\Form\SoutenanceMembre;

use Doctrine\ORM\EntityManager;
use Soutenance\Service\Membre\MembreService;
use Zend\Form\FormElementManager;
use Zend\Stdlib\Hydrator\HydratorPluginManager;


class SoutenanceMembreHydratorFactory
{
    public function __invoke(HydratorPluginManager $hydratorPluginManager)
    {
        $servicelocator = $hydratorPluginManager->getServiceLocator();
        /** @var MembreService $entityManager */
        $membreService = $servicelocator->get(MembreService::class);

        /** @var SoutenanceMembreForm $form */
        $hydrator = new SoutenanceMembreHydrator();
        $hydrator->setMembreService($membreService);

        return $hydrator;
    }
}