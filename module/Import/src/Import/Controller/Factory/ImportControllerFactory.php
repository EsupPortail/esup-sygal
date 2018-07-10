<?php

namespace Import\Controller\Factory;

use Doctrine\ORM\EntityManager;
use Import\Controller\ImportController;
use Import\Service\ImportService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportControllerFactory
{
    public function __invoke(ControllerManager $serviceLocator)
    {
        /** @var ServiceLocatorInterface $parentLocator */
        $parentLocator = $serviceLocator->getServiceLocator();

        /** @var EntityManager $entityManager */
        $entityManager = $parentLocator->get('doctrine.entitymanager.orm_default');

        /** @var ImportService $importService */
        $importService = $parentLocator->get(ImportService::class);

        $controller = new ImportController();
        $controller->setEntityManager($entityManager);
        $controller->setImportService($importService);

        return $controller;
    }
}