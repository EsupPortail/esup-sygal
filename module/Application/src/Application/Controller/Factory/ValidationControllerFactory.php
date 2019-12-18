<?php

namespace Application\Controller\Factory;

use Application\Controller\ValidationController;
use Application\Service\Notification\NotifierService;
use Application\Service\Role\RoleService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Validation\ValidationService;
use Application\Service\Variable\VariableService;
use Zend\Mvc\Controller\ControllerManager;

class ValidationControllerFactory
{
    /**
     * Create service
     *
     * @param ControllerManager $controllerManager
     * @return ValidationController
     */
    public function __invoke(ControllerManager $controllerManager)
    {
        /**
         * @var ValidationService $validationService
         * @var NotifierService $notifierService
         * @var RoleService $roleService
         * @var VariableService $variableService
         * @var UtilisateurService $utilisateurService
         */
        $validationService = $controllerManager->getServiceLocator()->get('ValidationService');
        $notifierService = $controllerManager->getServiceLocator()->get(NotifierService::class);
        $roleService = $controllerManager->getServiceLocator()->get('RoleService');
        $variableService = $controllerManager->getServiceLocator()->get('VariableService');
        $utilisateurService = $controllerManager->getServiceLocator()->get('UtilisateurService');

        $controller = new ValidationController();
        $controller->setValidationService($validationService);
        $controller->setNotifierService($notifierService);
        $controller->setRoleService($roleService);
        $controller->setVariableService($variableService);
        $controller->setUtilisateurService($utilisateurService);

        return $controller;
    }
}