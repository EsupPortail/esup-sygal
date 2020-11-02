<?php

namespace Application\Service\Utilisateur;

use Application\SourceCodeStringHelper;
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
         * @var UserService $userService
         */
        $userService = $container->get('unicaen-auth_user_service');

        /**
         * @var UserService $userService
         */
        $userService = $container->get('unicaen-auth_user_service');

        $service = new UtilisateurService();

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $container->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);
        $service->setUserService($userService);

        return $service;
    }
}
