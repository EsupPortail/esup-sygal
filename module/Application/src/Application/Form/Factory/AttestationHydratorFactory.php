<?php

namespace Application\Form\Factory;

use Application\Form\Hydrator\AttestationHydrator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AttestationHydratorFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $parentLocator = $serviceLocator->getServiceLocator();

        return new AttestationHydrator($parentLocator->get('doctrine.entitymanager.orm_default'));
    }
}
