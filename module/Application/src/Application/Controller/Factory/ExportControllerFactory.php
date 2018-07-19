<?php

namespace Application\Controller\Factory;

use Application\Controller\ExportController;
use Application\Service\Fichier\FichierService;
use Application\Service\These\TheseRechercheService;
use Application\Service\These\TheseService;
use Zend\Mvc\Controller\ControllerManager;

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
        /**
         * @var FichierService $fichierService
         * @var TheseService $theseService
         * @var TheseRechercheService $theseRechercheService
         */
        $fichierService = $controllerManager->getServiceLocator()->get('FichierService');
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $theseRechercheService = $controllerManager->getServiceLocator()->get('TheseRechercheService');

        $controller = new ExportController();
        $controller->setFichierService($fichierService);
        $controller->setTheseService($theseService);
        $controller->setTheseRechercheService($theseRechercheService);

        return $controller;
    }
}