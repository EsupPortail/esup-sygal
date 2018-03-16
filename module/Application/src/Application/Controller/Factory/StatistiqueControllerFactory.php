<?php

namespace Application\Controller\Factory;

use Application\Controller\StatistiqueController;
use Zend\Mvc\Controller\ControllerManager;

class StatistiqueControllerFactory
{
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();
        $theseService = $sl->get('TheseService');
        $ecoleService = $sl->get('EcoleDoctoraleService');
        $etabService  = $sl->get('EtablissementService');
        $uniteService = $sl->get('UniteRechercheService');

        $controller = new StatistiqueController();
        $controller->setTheseService($theseService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setEtablissementService($etabService);
        $controller->setUniteRechercheService($uniteService);
        return $controller;
    }
}