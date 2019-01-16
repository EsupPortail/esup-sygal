<?php

namespace Application\Controller\Factory;

use Application\Controller\StatistiqueController;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Structure\StructureService;
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
         * @var StructureService $structureService
         */
        $theseService = $sl->get('TheseService');
        $ecoleService = $sl->get('EcoleDoctoraleService');
        $etabService  = $sl->get('EtablissementService');
        $uniteService = $sl->get('UniteRechercheService');
        $structureService = $sl->get(StructureService::class);

        $controller = new StatistiqueController();
        $controller->setTheseService($theseService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setEtablissementService($etabService);
        $controller->setUniteRechercheService($uniteService);
        $controller->setStructureService($structureService);
        return $controller;
    }
}