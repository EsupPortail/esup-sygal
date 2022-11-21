<?php

namespace Depot\Controller\Factory;

use Application\Service\Notification\NotifierService;
use Application\Service\Role\RoleService;
use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Validation\ValidationService;
use Depot\Controller\ValidationController;
use Depot\Service\These\DepotService;
use Depot\Service\Validation\DepotValidationService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ValidationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ValidationController
    {
        /**
         * @var ValidationService $validationService
         * @var NotifierService $notifierService
         * @var RoleService $roleService
         * @var UtilisateurService $utilisateurService
         */
        $validationService = $container->get(ValidationService::class);
        $notifierService = $container->get(NotifierService::class);
        $roleService = $container->get('RoleService');
        $utilisateurService = $container->get('UtilisateurService');

        $controller = new ValidationController();
        $controller->setValidationService($validationService);
        $controller->setNotifierService($notifierService);
        $controller->setRoleService($roleService);
        $controller->setUtilisateurService($utilisateurService);

        /** @var DepotService $depotService */
        $depotService = $container->get(DepotService::class);
        $controller->setDepotService($depotService);

        /** @var \Depot\Service\Validation\DepotValidationService $depotValidationService */
        $depotValidationService = $container->get(DepotValidationService::class);
        $controller->setDepotValidationService($depotValidationService);

        return $controller;
    }
}