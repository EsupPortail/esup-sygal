<?php

namespace Application\Controller\Factory;

use Application\Controller\ExportController;
use Application\Service\Fichier\FichierService;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceLocatorInterface;

class ExportControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return ExportController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $parentLocator = $controllerManager->getServiceLocator();
        /** @var FichierService $fs */
        $fs = $parentLocator->get('FichierService');
        $service = new ExportController();
        $service->setFichierService($fs);


        return $service;
    }
}