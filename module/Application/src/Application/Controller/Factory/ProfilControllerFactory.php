<?php

namespace Application\Controller\Factory;

use Application\Controller\ProfilController;
use Application\Form\ProfilForm;
use Application\Service\Profil\ProfilService;
use Application\Service\Role\RoleService;
use Interop\Container\ContainerInterface;
use UnicaenAuth\Service\PrivilegeService;

class ProfilControllerFactory {

    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var PrivilegeService $privilegeService
         * @var ProfilService $profilService
         * @var RoleService $roleService
         */
        $privilegeService = $container->get('UnicaenAuth\Service\Privilege');
        $profilService = $container->get(ProfilService::class);
        $roleService = $container->get(RoleService::class);

        /** @var ProfilForm $profilForm */
        $profilForm = $container->get('FormElementManager')->get(ProfilForm::class);

        /** @var ProfilController $controller */
        $controller = new ProfilController();
        $controller->setServicePrivilege($privilegeService);
        $controller->setProfilService($profilService);
        $controller->setRoleService($roleService);
        $controller->setProfilForm($profilForm);

        return $controller;
    }
}