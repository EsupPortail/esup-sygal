<?php

namespace Application\Controller\Factory;

use Application\Controller\StructureController;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\Structure\StructureService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Zend\Mvc\Controller\ControllerManager;

class StructureControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return StructureController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /**
         * @var IndividuService $individuService
         * @var RoleService $roleService
         * @var StructureService $structureService
         * @var EcoleDoctoraleService $ecoleService
         * @var UniteRechercheService $uniteService
         * @var EtablissementService $etablissementService
         */

        $individuService = $sl->get('IndividuService');
        $roleService = $sl->get('RoleService');
        $structureService = $sl->get('StructureService');
        $ecoleService = $sl->get('EcoleDoctoraleService');
        $uniteService = $sl->get('UniteRechercheService');
        $etablissementService = $sl->get('EtablissementService');

        $controller = new StructureController();
        $controller->setIndividuService($individuService);
        $controller->setRoleService($roleService);
        $controller->setStructureService($structureService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setUniteRechercheService($uniteService);
        $controller->setEtablissementService($etablissementService);

        return $controller;
    }
}