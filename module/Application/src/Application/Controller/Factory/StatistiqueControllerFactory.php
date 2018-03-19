<?php

namespace Application\Controller\Factory;

use Application\Controller\StatistiqueController;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\These\TheseService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Zend\Mvc\Controller\ControllerManager;

class StatistiqueControllerFactory
{
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();
        /**
         * @var TheseService $theseService
         * @var EcoleDoctoraleService $ecoleService
         * @var EtablissementService $etabService
         * @var UniteRechercheService $uniteService
         */
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