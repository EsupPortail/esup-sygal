<?php


namespace Application\Form\Factory;

use Application\Form\Hydrator\PointsDeVigilanceHydrator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

class PointsDeVigilanceHydratorFactory
{

    public function __invoke(HydratorPluginManager $serviceLocator)
    {
        $parentLocator = $serviceLocator->getServiceLocator();
        $recapBuHydrator = new PointsDeVigilanceHydrator($parentLocator->get('doctrine.entitymanager.orm_default'));
        return $recapBuHydrator;
    }
}