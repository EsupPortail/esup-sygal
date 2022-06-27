<?php

namespace Application\Controller\Factory;

use Application\Controller\PrivilegeController;
use Structure\Service\Etablissement\EtablissementService;
use Application\Service\Role\RoleService;
use Structure\Service\Structure\StructureService;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;

class PrivilegeControllerFactory
{
    public function __invoke(ContainerInterface $container)
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
        $controller->setRoleService($roleService);
        $controller->setEtablissementService($etablissementService);
        $controller->setStructureService($structureService);

        return $controller;
    }
}