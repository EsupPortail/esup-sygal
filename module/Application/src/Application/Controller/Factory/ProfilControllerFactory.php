<?php

namespace Application\Controller\Factory;

use Application\Controller\ProfilController;
use Application\Form\ProfilForm;
use Application\Service\Profil\ProfilService;
use Application\Service\Role\RoleService;
use Interop\Container\ContainerInterface;
use UnicaenPrivilege\Service\Privilege\PrivilegeService;

class ProfilControllerFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var PrivilegeService $privilegeService
         * @var ProfilService $profilService
         * @var RoleService $roleService
         */
        $privilegeService = $container->get(\UnicaenPrivilege\Service\Privilege\PrivilegeService::class);
        $profilService = $container->get(ProfilService::class);
        $roleService = $container->get(RoleService::class);

        /** @var ProfilForm $profilForm */
        $profilForm = $container->get('FormElementManager')->get(ProfilForm::class);

        $controller = new ProfilController();
        $controller->setPrivilegeService($privilegeService);
        $controller->setProfilService($profilService);
        $controller->setApplicationRoleService($roleService);
        $controller->setProfilForm($profilForm);

        return $controller;
    }
}