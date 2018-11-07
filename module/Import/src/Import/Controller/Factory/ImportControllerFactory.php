<?php

namespace Import\Controller\Factory;

use Application\Service\These\TheseService;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Doctrine\ORM\EntityManager;
use Import\Controller\ImportController;
use Import\Service\ImportService;
use UnicaenApp\Exception\RuntimeException;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class ImportControllerFactory
{
    /**
     * @param ControllerManager $controllerManager
     * @return ImportController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /** @var ServiceLocatorInterface $parentLocator */
        $parentLocator = $controllerManager->getServiceLocator();

        /** @var EntityManager $entityManager */
        $entityManager = $parentLocator->get('doctrine.entitymanager.orm_default');

        /** @var ImportService $importService */
        $importService = $parentLocator->get(ImportService::class);

        /** @var TheseService $theseService */
        $theseService = $parentLocator->get('TheseService');

        try {
            $config = $this->getConfig($controllerManager->getServiceLocator());
        } catch (AssertionFailedException $e) {
            throw new RuntimeException("Configuration invalide", null, $e);
        }

        $controller = new ImportController();
        $controller->setEntityManager($entityManager);
        $controller->setImportService($importService);
        $controller->setTheseService($theseService);
        $controller->setConfig($config);

        return $controller;
    }

    /**
     * @param ServiceLocatorInterface $sl
     * @return array
     * @throws \Assert\AssertionFailedException
     */
    private function getConfig(ServiceLocatorInterface $sl)
    {
        $config = $sl->get('config');

        Assertion::keyIsset($config, 'import-api');

        return $config['import-api'];
    }
}