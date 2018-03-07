<?php


namespace Application\Form\Factory;

use Application\Form\Hydrator\RecapBuHydrator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class RecapBuHydratorFactory
{

    public function __invoke(HydratorPluginManager $serviceLocator)
    {
        $parentLocator = $serviceLocator->getServiceLocator();
        $recapBuHydrator = new RecapBuHydrator($parentLocator->get('doctrine.entitymanager.orm_default'));
        return $recapBuHydrator;
    }
}