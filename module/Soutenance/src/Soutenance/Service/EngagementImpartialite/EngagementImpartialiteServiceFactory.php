<?php

namespace Soutenance\Service\EngagementImpartialite;

use Application\Service\Utilisateur\UtilisateurService;
use Interop\Container\ContainerInterface;
use Soutenance\Service\Validation\ValidationService;

class EngagementImpartialiteServiceFactory
{
    /**
     * @param ContainerInterface $container
     * @return EngagementImpartialiteService
     */
    public function __invoke(ContainerInterface $container)
    {
        /**
         * @var ValidationService $validationService
         * @var UtilisateurService $utilisateurService
         */
        $validationService = $container->get(ValidationService::class);
        $utilisateurService = $container->get(UtilisateurService::class);

        /** @var EngagementImpartialiteService $service */
        $service = new EngagementImpartialiteService();
        $service->setValidationService($validationService);
        $service->setUtilisateurService($utilisateurService);
        return $service;
    }
}