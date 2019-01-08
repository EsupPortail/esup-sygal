<?php

namespace Application\Controller\Factory;

use Application\Controller\ProfilController;
use Application\Service\Profil\ProfilService;
use Application\Service\Role\RoleService;
use UnicaenAuth\Service\PrivilegeService;
use Zend\Mvc\Controller\ControllerManager;

class ProfilControllerFactory {

    public function __invoke(ControllerManager $controllerManager) {

        /**
         * @var PrivilegeService $privilegeService
         * @var ProfilService $profilService
         * @var RoleService $roleService
         */
        $privilegeService = $controllerManager->getServiceLocator()->get('UnicaenAuth\Service\Privilege');
        $profilService = $controllerManager->getServiceLocator()->get(ProfilService::class);
        $roleService = $controllerManager->getServiceLocator()->get(RoleService::class);

        /** @var ProfilController $controller */
        $controller = new ProfilController();
        $controller->setServicePrivilege($privilegeService);
        $controller->setProfilService($profilService);
        $controller->setRoleService($roleService);
        return $controller;
    }
}