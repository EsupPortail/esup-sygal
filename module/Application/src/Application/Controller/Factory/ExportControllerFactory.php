<?php

namespace Application\Controller\Factory;

use Application\Controller\ExportController;
use Application\Service\Fichier\FichierService;
use Application\Service\These\TheseRechercheService;
use Application\Service\These\TheseService;
use Application\SourceCodeStringHelper;
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
        $sl = $controllerManager->getServiceLocator();

        /**
         * @var FichierService $fichierService
         * @var TheseService $theseService
         * @var TheseRechercheService $theseRechercheService
         */
        $fichierService = $sl->get('FichierService');
        $theseService = $sl->get('TheseService');
        $theseRechercheService = $sl->get('TheseRechercheService');

        $controller = new ExportController();
        $controller->setFichierService($fichierService);
        $controller->setTheseService($theseService);
        $controller->setTheseRechercheService($theseRechercheService);

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $sl->get(SourceCodeStringHelper::class);
        $controller->setSourceCodeStringHelper($sourceCodeHelper);

        return $controller;
    }
}