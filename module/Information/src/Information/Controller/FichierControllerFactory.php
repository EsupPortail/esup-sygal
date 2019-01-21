<?php

namespace  Information\Controller;

use Application\Service\Fichier\FichierService;
use Zend\Mvc\Controller\ControllerManager;

class FichierControllerFactory {

    public function __invoke(ControllerManager $manager)
    {
        /** @var FichierService $fichierService */
        $fichierService = $manager->getServiceLocator()->get('FichierService');

        $controller = new FichierController();
        $controller->setFichierService($fichierService);
        return $controller;
    }
}