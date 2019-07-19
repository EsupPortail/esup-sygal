<?php

namespace Application\Controller\Factory;

use Application\Controller\FichierController;
use Application\Service\Fichier\FichierService;
use Zend\Mvc\Controller\ControllerManager;

class FichierControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return FichierController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /** @var FichierService $fichierService */
        $fichierService = $sl->get(FichierService::class);

        $service = new FichierController();
        $service->setFichierService($fichierService);

        return $service;
    }
}