<?php

namespace Application\Controller\Factory;

use Application\Controller\PrivilegeController;
use Structure\Service\Etablissement\EtablissementService;
use Application\Service\Role\RoleService;
use Structure\Service\Structure\StructureService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use UnicaenPrivilege\Service\Privilege\PrivilegeCategorieService;

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

        /** @var PrivilegeCategorieService $categoriesPrivilegeService */
        $categoriesPrivilegeService = $container->get(PrivilegeCategorieService::class);
        $controller->setprivilegeCategorieService($categoriesPrivilegeService);

        return $controller;
    }
}