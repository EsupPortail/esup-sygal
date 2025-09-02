<?php

namespace Application\Controller\Factory;

use Application\Controller\PrivilegeController;
use Application\Service\Role\RoleService;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Structure\Service\Etablissement\EtablissementService;
use Structure\Service\Structure\StructureService;
use UnicaenPrivilege\Service\Privilege\PrivilegeCategorieService;
use UnicaenPrivilege\Service\Privilege\PrivilegeService;

class PrivilegeControllerFactory
{
    /**
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function __invoke(ContainerInterface $container): PrivilegeController
    {
        /**
         * @var EntityManager $entityManager
         * @var RoleService $roleService
         * @var EtablissementService $etablissementService
         * @var StructureService $structureService
         */
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $roleService = $container->get('RoleService');
        $etablissementService = $container->get('EtablissementService');
        $structureService = $container->get('StructureService');

        $controller = new PrivilegeController();
        $controller->setEntityManager($entityManager);
        $controller->setApplicationRoleService($roleService);
        $controller->setEtablissementService($etablissementService);
        $controller->setStructureService($structureService);

        /** @var RoleService $roleService */
        $roleService = $container->get(RoleService::class);
        $controller->setApplicationRoleService($roleService);

        /** @var PrivilegeService $privilegeService */
        $privilegeService = $container->get(PrivilegeService::class);
        $controller->setPrivilegeService($privilegeService);

        /** @var PrivilegeCategorieService $categoriesPrivilegeService */
        $categoriesPrivilegeService = $container->get(PrivilegeCategorieService::class);
        $controller->setprivilegeCategorieService($categoriesPrivilegeService);

        return $controller;
    }
}