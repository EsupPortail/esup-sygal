<?php

namespace Application\Controller\Factory;

use Application\Controller\TheseFiltersController;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\These\TheseService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Zend\Mvc\Controller\ControllerManager;

class TheseFiltersControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return TheseFiltersController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /**
         * @var TheseService $theseService
         * @var EtablissementService $etablissementService
         * @var UniteRechercheService $uniteService
         * @var EcoleDoctoraleService $ecoleDoctoraleService
         */
        $theseService = $controllerManager->getServiceLocator()->get('TheseService');
        $ecoleDoctoraleService = $controllerManager->getServiceLocator()->get('EcoleDoctoraleService');
        $uniteService = $controllerManager->getServiceLocator()->get('UniteRechercheService');
        $etablissementService = $controllerManager->getServiceLocator()->get('EtablissementService');

        $controller = new TheseFiltersController();
        $controller->setTheseService($theseService);
        $controller->setEtablissementService($etablissementService);
        $controller->setUniteRechercheService($uniteService);
        $controller->setEcoleDoctoraleService($ecoleDoctoraleService);

        return $controller;
    }
}