<?php

namespace Application\Service\RapportAnnuel;

use Application\Search\Filter\Provider\SearchFilterProviderService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface as ContainerInterface;

class RapportAnnuelSearchServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @return RapportAnnuelSearchService
     */
    public function createService(ContainerInterface $container)
    {
        /**
         * @var RapportAnnuelService $rapportAnnuelService
         * @var SearchFilterProviderService $searchFilterProviderService
         */
        $rapportAnnuelService = $container->get(RapportAnnuelService::class);
        $searchFilterProviderService = $container->get(SearchFilterProviderService::class);

        $service = new RapportAnnuelSearchService();
        $service->setRapportAnnuelService($rapportAnnuelService);
        $service->setSearchFilterProviderService($searchFilterProviderService);

        return $service;
    }
}