<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\EcoleDoctoraleHydrator;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class EcoleDoctoraleHydratorFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(HydratorPluginManager $hydratorPluginManager)
    {
        $parentLocator = $hydratorPluginManager->getServiceLocator();

        return new EcoleDoctoraleHydrator($parentLocator->get('doctrine.entitymanager.orm_default'));
    }
}
