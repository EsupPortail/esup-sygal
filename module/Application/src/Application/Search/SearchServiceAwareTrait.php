<?php

namespace Application\Search;

trait SearchServiceAwareTrait
{
    /**
     * @var SearchServiceInterface
     */
    protected $searchService;

    /**
     * @param SearchServiceInterface $searchService
     */
    public function setSearchService(SearchServiceInterface $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * @return SearchServiceInterface
     */
    public function getSearchService()
    {
        return $this->searchService;
    }
}