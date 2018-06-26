<?php

namespace Indicateur\Controller\Factory;

use Application\Service\AnomalieService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuService;
use Application\Service\These\TheseService;
use Indicateur\Controller\IndicateurController;
use Zend\Mvc\Controller\ControllerManager;

class IndicateurControllerFactory
{
    /**
     * @param ControllerManager $controllerManager
     * @return IndicateurController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /**
         * @var IndividuService $individuService
         * @var TheseService $theseService
         * @var AnomalieService $anomalieService
         * @var EtablissementService $etablissementService
         */
        $individuService = $controllerManager->getServiceLocator()->get('IndividuService');
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $anomalieService = $controllerManager->getServiceLocator()->get(AnomalieService::class);
        $etablissementService = $controllerManager->getServiceLocator()->get('EtablissementService');

        $controller = new IndicateurController();
        $controller->setIndividuService($individuService);
        $controller->setTheseService($theseService);
        $controller->setAnomalieService($anomalieService);
        $controller->setEtablissementService($etablissementService);

        return $controller;
    }
}