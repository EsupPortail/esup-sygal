<?php

namespace Import\Controller\Factory;

use Import\Controller\ImportController;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportControllerFactory
{
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        $parentLocator = $serviceLocator->getServiceLocator();
        $entityManager = $serviceLocator->get('doctrine.entitymanager.orm_default');
        $fetcherService = $parentLocator->get(\Import\Service\FetcherService::class);
        $controller =  new ImportController($fetcherService);
        $controller->setEntityManager($entityManager);
        return $controller;
    }
}