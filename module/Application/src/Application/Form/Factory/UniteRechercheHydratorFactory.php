<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\UniteRechercheHydrator;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class UniteRechercheHydratorFactory
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(HydratorPluginManager $hydratorPluginManager)
    {
        $parentLocator = $hydratorPluginManager->getServiceLocator();

        return new UniteRechercheHydrator($parentLocator->get('doctrine.entitymanager.orm_default'));
    }
}
