<?php

namespace Application\Controller\Factory;

use Application\Controller\StructureController;
use Application\Service\Individu\IndividuService;
use Application\Service\Role\RoleService;
use Application\Service\Structure\StructureService;
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
         */

        $individuService = $sl->get('IndividuService');
        $roleService = $sl->get('RoleService');
        $structureService = $sl->get('StructureService');

        $controller = new StructureController();
        $controller->setIndividuService($individuService);
        $controller->setRoleService($roleService);
        $controller->setStructureService($structureService);

        return $controller;
    }
}