<?php

namespace Application\Controller\Factory;

use Application\Controller\ExportController;
use Application\Service\FichierThese\FichierTheseService;
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
         * @var FichierTheseService   $fichierTheseService
         * @var TheseService          $theseService
         * @var TheseRechercheService $theseRechercheService
         */
        $fichierTheseService = $sl->get('FichierTheseService');
        $theseService = $sl->get('TheseService');
        $theseRechercheService = $sl->get('TheseRechercheService');

        $controller = new ExportController();
        $controller->setFichierTheseService($fichierTheseService);
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