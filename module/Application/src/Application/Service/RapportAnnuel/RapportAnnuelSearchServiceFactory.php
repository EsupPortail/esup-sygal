<?php

namespace Application\Service\RapportAnnuel;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RapportAnnuelSearchServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return RapportAnnuelSearchService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = new RapportAnnuelSearchService();

        return $service;
    }
}