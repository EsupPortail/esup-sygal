<?php

namespace Application\Service\Email;

use Application\Service\Utilisateur\UtilisateurService;
use Application\Service\Variable\VariableService;
use Psr\Container\ContainerInterface;

class EmailTheseServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return EmailTheseService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): EmailTheseService
    {
        $roleService = $container->get('RoleService');
        $utilisateurService = $container->get(UtilisateurService::class);
        $variableService = $container->get(VariableService::class);

        $service = new EmailTheseService();
        $service->setRoleService($roleService);
        $service->setUtilisateurService($utilisateurService);
        $service->setVariableService($variableService);

        return $service;
    }
}
