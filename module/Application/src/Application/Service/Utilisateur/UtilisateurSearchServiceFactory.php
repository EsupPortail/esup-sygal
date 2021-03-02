<?php

namespace Application\Service\Utilisateur;

use Application\Search\Filter\Provider\SearchFilterProviderService;
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
         * @var SearchFilterProviderService $searchFilterProviderService
         */
        $utilisateurService = $container->get(UtilisateurService::class);
        $searchFilterProviderService = $container->get(SearchFilterProviderService::class);

        $service = new UtilisateurSearchService();
        $service->setUtilisateurService($utilisateurService);
        $service->setSearchFilterProviderService($searchFilterProviderService);

        return $service;
    }
}