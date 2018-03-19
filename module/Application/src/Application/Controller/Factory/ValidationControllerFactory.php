<?php

namespace Application\Controller\Factory;

use Application\Controller\ValidationController;
use Application\Service\Notification\NotificationService;
use Application\Service\Role\RoleService;
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
         * @var NotificationService $notificationService
         * @var RoleService $roleService
         * @var VariableService $variableService
         */
        $validationService = $controllerManager->getServiceLocator()->get('ValidationService');
        $notificationService = $controllerManager->getServiceLocator()->get('NotificationService');
        $roleService = $controllerManager->getServiceLocator()->get('RoleService');
        $variableService = $controllerManager->getServiceLocator()->get('VariableService');

        $controller = new ValidationController();
        $controller->setValidationService($validationService);
        $controller->setNotificationService($notificationService);
        $controller->setRoleService($roleService);
        $controller->setVariableService($variableService);

        return $controller;
    }
}