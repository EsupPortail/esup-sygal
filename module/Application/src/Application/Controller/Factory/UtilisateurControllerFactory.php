<?php

namespace Application\Controller\Factory;

use Application\Controller\UtilisateurController;
use Application\Service\Role\RoleService;
use Application\Service\Utilisateur\UtilisateurService;
use UnicaenLdap\Service\People as LdapPeopleService;
use Zend\Mvc\Controller\ControllerManager;


class UtilisateurControllerFactory
{
    public function __invoke(ControllerManager $controllerManager)
    {

        /**
         * @var RoleService $roleService
         * @var UtilisateurService $utilisateurService
         * @var LdapPeopleService $ldapPeopleService
         */
        $roleService = $controllerManager->getServiceLocator()->get('RoleService');
        $ldapPeopleService  = $controllerManager->getServiceLocator()->get('LdapServicePeople');
        $utilisateurService = $controllerManager->getServiceLocator()->get('UtilisateurService');

        $controller = new UtilisateurController();
        $controller->setRoleService($roleService);
        $controller->setLdapPeopleService($ldapPeopleService);
        $controller->setUtilisateurService($utilisateurService);

        return $controller;
    }
}
