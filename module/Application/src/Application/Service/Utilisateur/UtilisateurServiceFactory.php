<?php

namespace Application\Service\Utilisateur;

use Application\Service\Source\SourceService;
use Interop\Container\ContainerInterface;
use UnicaenAuth\Service\User as UserService;

class UtilisateurServiceFactory
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return UtilisateurService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var SourceService $sourceService
         */
        $sourceService = $container->get(SourceService::class);

        /**
         * @var UserService $userService
         */
        $userService = $container->get('unicaen-auth_user_service');

        $service = new UtilisateurService();

        $service->setUserService($userService);
        $service->setSourceService($sourceService);

        return $service;
    }
}
