<?php

namespace Application\Controller\Factory;

use Application\Controller\EtablissementController;
use Application\Form\EtablissementForm;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\Structure\StructureService;
use Zend\Mvc\Controller\ControllerManager;

class EtablissementControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return EtablissementController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /** @var EtablissementForm $form */
        $form = $sl->get('FormElementManager')->get('EtablissementForm');

        /**
         * @var EtablissementService $etablissmentService
         * @var IndividuService $individuService
         * @var RoleService $roleService
         * @var StructureService $structureService
         */
        $etablissmentService = $sl->get('EtablissementService');
        $roleService = $sl->get('RoleService');
        $structureService = $sl->get(StructureService::class);

        $controller = new EtablissementController();
        $controller->setEtablissementService($etablissmentService);
        $controller->setRoleService($roleService);
        $controller->setStructureService($structureService);
        $controller->setStructureForm($form);

        return $controller;
    }
}