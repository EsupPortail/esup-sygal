<?php

namespace  Information\Controller;

use Application\Service\Fichier\FichierService;
use Information\Service\InformationFichierService;
use Zend\Mvc\Controller\ControllerManager;

class FichierControllerFactory {

    public function __invoke(ControllerManager $manager)
    {
        /**
         * @var FichierService            $fichierService
         * @var InformationFichierService $informationFichierService
         */
        $fichierService = $manager->getServiceLocator()->get(FichierService::class);
        $informationFichierService = $manager->getServiceLocator()->get(InformationFichierService::class);

        $controller = new FichierController();
        $controller->setFichierService($fichierService);
        $controller->setInformationFichierService($informationFichierService);

        return $controller;
    }
}