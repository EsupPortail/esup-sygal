<?php

namespace Application\Search;

trait SearchServiceAwareTrait
{
    protected SearchServiceInterface $searchService;

    public function setSearchService(SearchServiceInterface $searchService): void
    {
        $this->searchService = $searchService;
    }

    public function getSearchService(): SearchServiceInterface
    {
        return $this->searchService;
    }
}