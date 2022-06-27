<?php

namespace Application\Controller\Factory;

use Application\Controller\RoleController;
use Structure\Service\Etablissement\EtablissementService;
use Application\Service\Role\RoleService;
use Interop\Container\ContainerInterface;

class RoleControllerFactory
{
    /**
     * @param ContainerInterface $container
     * @return RoleController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var RoleService $roleService
         * @var EtablissementService $etablissementService
         */
        $roleService = $container->get('RoleService');
        $etablissementService = $container->get(EtablissementService::class);

        $controller = new RoleController();
        $controller->setRoleService($roleService);
        $controller->setEtablissementService($etablissementService);

        return $controller;
    }
}