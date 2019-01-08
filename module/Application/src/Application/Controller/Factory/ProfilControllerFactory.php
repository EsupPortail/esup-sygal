<?php

namespace Application\Controller\Factory;

use Application\Controller\ProfilController;
use Application\Service\Profil\ProfilService;
use UnicaenAuth\Service\PrivilegeService;
use Zend\Mvc\Controller\ControllerManager;

class ProfilControllerFactory {

    public function __invoke(ControllerManager $controllerManager) {

        /**
         * @var PrivilegeService $privilegeService
         * @var ProfilService $profilService
         */
        $privilegeService = $controllerManager->getServiceLocator()->get('UnicaenAuth\Service\Privilege');
        $profilService = $controllerManager->getServiceLocator()->get(ProfilService::class);

        /** @var ProfilController $controller */
        $controller = new ProfilController();
        $controller->setServicePrivilege($privilegeService);
        $controller->setProfilService($profilService);
        return $controller;
    }
}