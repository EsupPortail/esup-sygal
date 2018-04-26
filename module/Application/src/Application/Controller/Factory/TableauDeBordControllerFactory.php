<?php

namespace Application\Controller\Factory;

use Application\Controller\TableauDeBordController;
use Application\Service\AnomalieService;
use Application\Service\Etablissement\EtablissementService;
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
        /**
         * @var AnomalieService $anomalieService
         * @var EtablissementService $etablissementService
         */
        $anomalieService= $controllerManager->getServiceLocator()->get(AnomalieService::class);
        $etablissementService= $controllerManager->getServiceLocator()->get('EtablissementService');

        $controller = new TableauDeBordController();
        $controller->setAnomalieService($anomalieService);
        $controller->setEtablissementService($etablissementService);

        return $controller;
    }
}
