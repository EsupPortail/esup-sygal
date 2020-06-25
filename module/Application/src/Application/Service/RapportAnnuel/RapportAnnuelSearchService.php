<?php

namespace Application\Service\RapportAnnuel;

use Application\Search\SearchServiceInterface;
use Application\Search\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;

class RapportAnnuelSearchService implements SearchServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getFilterNames()
    {
        // TODO: Implement getFilterNames() method.
    }

    /**
     * @inheritDoc
     */
    public function fetchValueOptionsForFilter($filterName)
    {
        // TODO: Implement fetchValueOptionsForFilter() method.
    }

    /**
     * @inheritDoc
     */
    public function updateQueryParamsWithDefaultFilters($queryParams)
    {
        // TODO: Implement updateQueryParamsWithDefaultFilters() method.
    }

    /**
     * @inheritDoc
     */
    public function updateQueryParamsWithDefaultSorters($queryParams)
    {
        // TODO: Implement updateQueryParamsWithDefaultSorters() method.
    }

    /**
     * @inheritDoc
     */
    public function createFiltersWithUnpopulatedOptions()
    {
        // TODO: Implement createFiltersWithUnpopulatedOptions() method.
    }

    /**
     * @inheritDoc
     */
    public function createSorters()
    {
        // TODO: Implement createSorters() method.
    }

    /**
     * @inheritDoc
     */
    public function processQueryParams($queryParams)
    {
        // TODO: Implement processQueryParams() method.
    }

    /**
     * @inheritDoc
     */
    public function getFilterValueByName($name)
    {
        // TODO: Implement getFilterValueByName() method.
    }

    /**
     * @inheritDoc
     */
    public function createQueryBuilder()
    {
        // TODO: Implement createQueryBuilder() method.
    }

    /**
     * @inheritDoc
     */
    public function createFilters()
    {
        // TODO: Implement createFilters() method.
    }

    /**
     * @inheritDoc
     */
    public function getFilters()
    {
        // TODO: Implement getFilters() method.
    }
}