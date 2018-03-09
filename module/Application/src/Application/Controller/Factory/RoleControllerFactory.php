<?php

namespace Application\Controller\Factory;

use Application\Controller\RoleController;
use UnicaenAuth\Service\PrivilegeService;
use Zend\Mvc\Controller\ControllerManager;
//use UnicaenAuth\Service\Traits\PrivilegeServiceAwareTrait;

class RoleControllerFactory
{
//    use PrivilegeServiceAwareTrait;

    public function __invoke(ControllerManager $controllerManager)
    {

//        $service = $controllerManager->getServiceLocator()->get(PrivilegeService::class);
        $controller = new RoleController();
//        $controller->setServicePrivilege($service);

        return $controller;
    }
}