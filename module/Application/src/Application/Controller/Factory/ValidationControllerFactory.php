<?php

namespace Application\Controller\Factory;

use Application\Controller\ValidationController;
use Application\Service\Notification\NotifierService;
use Application\Service\Role\RoleService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Validation\ValidationService;
use Application\Service\Variable\VariableService;
use Interop\Container\ContainerInterface;

class ValidationControllerFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return ValidationController
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ValidationService $validationService
         * @var NotifierService $notifierService
         * @var RoleService $roleService
         * @var VariableService $variableService
         * @var UtilisateurService $utilisateurService
         */
        $validationService = $container->get('ValidationService');
        $notifierService = $container->get(NotifierService::class);
        $roleService = $container->get('RoleService');
        $variableService = $container->get('VariableService');
        $utilisateurService = $container->get('UtilisateurService');

        $controller = new ValidationController();
        $controller->setValidationService($validationService);
        $controller->setNotifierService($notifierService);
        $controller->setRoleService($roleService);
        $controller->setVariableService($variableService);
        $controller->setUtilisateurService($utilisateurService);

        return $controller;
    }
}