<?php

namespace Application\Controller\Factory;

use Application\Controller\RoleController;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Role\RoleService;
use Application\Service\Structure\StructureService;
use Doctrine\ORM\EntityManager;
use Zend\Mvc\Controller\ControllerManager;


class RoleControllerFactory
{
    public function __invoke(ControllerManager $controllerManager)
    {

        /**
         * @var EntityManager $entityManager
         * @var RoleService $roleService
         * @var EtablissementService $etablissementService
         * @var StructureService $structureService
         */
        $entityManager = $controllerManager->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $roleService = $controllerManager->getServiceLocator()->get('RoleService');
        $etablissementService = $controllerManager->getServiceLocator()->get('EtablissementService');
        $structureService = $controllerManager->getServiceLocator()->get('StructureService');

        $controller = new RoleController();
        $controller->setEntityManager($entityManager);
        $controller->setRoleService($roleService);
        $controller->setEtablissementService($etablissementService);
        $controller->setStructureService($structureService);

        return $controller;
    }
}