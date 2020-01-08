<?php

namespace Application\Service\Utilisateur;

use Application\SourceCodeStringHelper;
use UnicaenAuth\Entity\Db\User as UserService;
use Zend\ServiceManager\ServiceLocatorInterface;

class UtilisateurServiceFactory
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return UtilisateurService
     */
    public function __invoke(ServiceLocatorInterface $serviceLocator)
    {

        /**
         * @var UserService $userService
         */
        $userService = $serviceLocator->get('unicaen-auth_user_service');

        /**
         * @var UserService $userService
         */
        $userService = $serviceLocator->get('unicaen-auth_user_service');


        $service = new UtilisateurService();

        /**
         * @var SourceCodeStringHelper $sourceCodeHelper
         */
        $sourceCodeHelper = $serviceLocator->get(SourceCodeStringHelper::class);
        $service->setSourceCodeStringHelper($sourceCodeHelper);
        $service->setUserService($userService);

        return $service;
    }
}
