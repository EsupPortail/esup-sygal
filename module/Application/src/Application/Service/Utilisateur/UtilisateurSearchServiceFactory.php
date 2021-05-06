<?php

namespace Application\Service\Utilisateur;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class UtilisateurSearchServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return UtilisateurSearchService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UtilisateurSearchService
    {
        /**
         * @var UtilisateurService $utilisateurService
         */
        $utilisateurService = $container->get(UtilisateurService::class);

        $service = new UtilisateurSearchService();
        $service->setUtilisateurService($utilisateurService);

        return $service;
    }
}