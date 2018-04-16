<?php

namespace Application\Controller\Factory;

use Application\Controller\SubstitutionController;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Structure\StructureService;
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
         * @var EtablissementService $etablissementService
         * @var StructureService $structureService
         */
        $etablissementService = $sl->get('EtablissementService');
        $structureService = $sl->get('StructureService');

        $controller = new SubstitutionController();
        $controller->setEtablissementService($etablissementService);
        $controller->setStructureService($structureService);

        return $controller;
    }
}