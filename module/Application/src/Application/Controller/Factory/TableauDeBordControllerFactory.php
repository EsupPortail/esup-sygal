<?php

namespace Application\Controller\Factory;

use Application\Controller\TableauDeBordController;
use Application\Service\AnomalieService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Source\SourceService;
use Zend\Mvc\Controller\ControllerManager;

class TableauDeBordControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return TableauDeBordController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /** @var SourceService $sourceService */
        $sourceService = $sl->get(SourceService::class);

        /**
         * @var AnomalieService $anomalieService
         * @var EtablissementService $etablissementService
         */
        $anomalieService= $sl->get(AnomalieService::class);
        $etablissementService= $sl->get('EtablissementService');

        $controller = new TableauDeBordController();
        $controller->setAnomalieService($anomalieService);
        $controller->setEtablissementService($etablissementService);
        $controller->setSourceService($sourceService);

        return $controller;
    }
}
