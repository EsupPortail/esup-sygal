<?php

namespace Application\Search\Filter\Provider;

trait SearchFilterProviderServiceAwareTrait
{
    /**
     * @var SearchFilterProviderService
     */
    protected $searchFilterProviderService;

    /**
     * @param SearchFilterProviderService $searchFilterProviderService
     */
    public function setSearchFilterProviderService(SearchFilterProviderService $searchFilterProviderService)
    {
        $this->searchFilterProviderService = $searchFilterProviderService;
    }
}