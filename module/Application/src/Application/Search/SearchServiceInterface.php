<?php

namespace Application\Search;

use Application\Search\Filter\SearchFilter;

interface SearchServiceInterface
{
    /**
     * Initialisation nécessaires, ex: ajouts des filtres, des trieurs.
     */
    public function init();

    /**
     * @param array $queryParams
     * @return bool
     */
    public function updateQueryParamsWithDefaultFilters(array &$queryParams);

    /**
     * @param array $queryParams
     * @return bool
     */
    public function updateQueryParamsWithDefaultSorters(array &$queryParams);

    /**
     * @return self
     */
    public function initFiltersWithUnpopulatedOptions();

    /**
     * @return self
     */
    public function initFilters();

    /**
     * @param array $queryParams
     * @return self
     */
    public function processQueryParams(array $queryParams);

    /**
     * @param $name
     * @return string|null
     */
    public function getFilterValueByName($name);

    /**
     * @return SearchFilter[]
     */
    public function getFilters();
}