<?php

namespace Application\Navigation;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
 
class NavigationFactoryFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $navigation = new ApplicationNavigationFactory();
        return $navigation->createService($serviceLocator);
    }
}