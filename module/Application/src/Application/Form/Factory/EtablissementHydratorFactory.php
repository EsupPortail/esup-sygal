<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\EtablissementHydrator;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class EtablissementHydratorFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(HydratorPluginManager $hydratorPluginManager)
    {
        $parentLocator = $hydratorPluginManager->getServiceLocator();

        return new EtablissementHydrator($parentLocator->get('doctrine.entitymanager.orm_default'));
    }
}
