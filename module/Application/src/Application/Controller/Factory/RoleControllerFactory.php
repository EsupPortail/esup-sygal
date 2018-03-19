<?php

namespace Application\Controller\Factory;

use Application\Controller\RoleController;
use Application\Service\Etablissement\EtablissementService;
use Application\Service\Role\RoleService;
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
         */
        $entityManager = $controllerManager->getServiceLocator()->get('doctrine.entitymanager.orm_default');
        $roleService = $controllerManager->getServiceLocator()->get('RoleService');
        $etablissementService = $controllerManager->getServiceLocator()->get('EtablissementService');

        $controller = new RoleController();
        $controller->setEntityManager($entityManager);
        $controller->setRoleService($roleService);
        $controller->setEtablissementService($etablissementService);

        return $controller;
    }
}