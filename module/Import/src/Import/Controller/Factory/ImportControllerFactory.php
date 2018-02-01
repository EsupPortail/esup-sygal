<?php

namespace Import\Controller\Factory;

use Import\Controller\ImportController;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportControllerFactory
{
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {
        $parentLocator = $serviceLocator->getServiceLocator();
        $fetcherService = $parentLocator->get(\Import\Service\FetcherService::class);
        return new ImportController(
                $fetcherService
        );
    }
}