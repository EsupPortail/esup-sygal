<?php

namespace Application\Service\RapportAnnuel;

use Application\Search\Filter\Provider\SearchFilterProviderService;
use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class RapportAnnuelSearchServiceFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return RapportAnnuelSearchService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
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