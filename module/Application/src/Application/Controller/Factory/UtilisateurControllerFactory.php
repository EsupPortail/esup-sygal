<?php

namespace Application\Controller\Factory;

use Application\Controller\UtilisateurController;
use Application\Service\Individu\IndividuServiceLocateTrait;
use Application\Service\Role\RoleService;
use Application\Service\Utilisateur\UtilisateurService;
use Doctrine\ORM\EntityManager;
use UnicaenLdap\Service\People as LdapPeopleService;
use Zend\Mvc\Controller\ControllerManager;

class UtilisateurControllerFactory
{
    use IndividuServiceLocateTrait;

    public function __invoke(ControllerManager $controllerManager)
    {
        $sl = $controllerManager->getServiceLocator();

        /**
         * @var RoleService $roleService
         * @var UtilisateurService $utilisateurService
         * @var LdapPeopleService $ldapPeopleService
         * @var EntityManager $entityManager;
         */
        $roleService = $sl->get('RoleService');
        $ldapPeopleService  = $sl->get('LdapServicePeople');
        $utilisateurService = $sl->get('UtilisateurService');
        $entityManager = $sl->get('doctrine.entitymanager.orm_default');

        $controller = new UtilisateurController();
        $controller->setRoleService($roleService);
        $controller->setLdapPeopleService($ldapPeopleService);
        $controller->setUtilisateurService($utilisateurService);
        $controller->setIndividuService($this->locateIndividuService($sl));
        $controller->setEntityManager($entityManager);

        return $controller;
    }
}
