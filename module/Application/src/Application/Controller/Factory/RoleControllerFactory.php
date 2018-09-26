<?php

namespace Application\Controller\Factory;

use Application\Controller\RoleController;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Role\RoleService;
use Zend\Mvc\Controller\ControllerManager;

class RoleControllerFactory
{

    /**
     * @param ControllerManager $controllerManager
     * @return RoleController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /**
         * @var RoleService $roleService
         * @var EtablissementService $etablissementService
         */
        $roleService = $controllerManager->getServiceLocator()->get('RoleService');
        $etablissementService = $controllerManager->getServiceLocator()->get(EtablissementService::class);

        $controller = new RoleController();
        $controller->setRoleService($roleService);
        $controller->setEtablissementService($etablissementService);

        return $controller;
    }
}