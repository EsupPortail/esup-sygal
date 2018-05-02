<?php

namespace Application\Controller\Factory;

use Application\Controller\SubstitutionController;
use Application\Service\EcoleDoctorale\EcoleDoctoraleService;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Structure\StructureService;
use Application\Service\UniteRecherche\UniteRechercheService;
use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\ControllerManager;

class SubstitutionControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return SubstitutionController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /**
         * @var EntityManager $entityManager
         * @var EtablissementService $etablissementService
         * @var StructureService $structureService
         * @var EcoleDoctoraleService $ecoleService
         * @var UniteRechercheService $uniteService
         */
        $entityManager = $sl->get('doctrine.entitymanager.orm_default');
        $etablissementService = $sl->get('EtablissementService');
        $ecoleService = $sl->get('EcoleDoctoraleService');
        $uniteService = $sl->get('UniteRechercheService');
        $structureService = $sl->get('StructureService');

        $controller = new SubstitutionController();
        $controller->setEntityManager($entityManager);
        $controller->setEtablissementService($etablissementService);
        $controller->setEcoleDoctoraleService($ecoleService);
        $controller->setUniteRechercheService($uniteService);
        $controller->setStructureService($structureService);

        return $controller;
    }
}