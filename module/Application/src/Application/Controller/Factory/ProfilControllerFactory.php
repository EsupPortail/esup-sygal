<?php

namespace Application\Controller\Factory;

use Application\Controller\ProfilController;
use Application\Form\ProfilForm;
use Application\Service\Profil\ProfilService;
use Application\Service\Role\RoleService;
use Interop\Container\ContainerInterface;
use UnicaenPrivilege\Service\Privilege\PrivilegeCategorieService;
use UnicaenPrivilege\Service\Privilege\PrivilegeService;

class ProfilControllerFactory {

    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): ProfilController
    {
        /**
         * @var PrivilegeService $privilegeService
         * @var ProfilService $profilService
         * @var RoleService $roleService
         */
        $privilegeService = $container->get(PrivilegeService::class);
        $profilService = $container->get(ProfilService::class);
        $roleService = $container->get(RoleService::class);

        /** @var ProfilForm $profilForm */
        $profilForm = $container->get('FormElementManager')->get(ProfilForm::class);

        $controller = new ProfilController();
        $controller->setPrivilegeService($privilegeService);
        $controller->setProfilService($profilService);
        $controller->setApplicationRoleService($roleService);
        $controller->setProfilForm($profilForm);

        /** @var PrivilegeCategorieService $categoriesPrivilegeService */
        $categoriesPrivilegeService = $container->get(PrivilegeCategorieService::class);
        $controller->setprivilegeCategorieService($categoriesPrivilegeService);

        return $controller;
    }
}