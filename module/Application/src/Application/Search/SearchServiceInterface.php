<?php

namespace Application\Search;

use Application\Search\Filter\SearchFilter;

interface SearchServiceInterface
{
    /**
     * Initialisation nécessaires, ex: ajouts des filtres, des trieurs.
     * @return void
     */
    public function init();

    /**
     * @param array $queryParams
     * @return bool
     */
    public function updateQueryParamsWithDefaultFilters(array &$queryParams): bool;

    /**
     * @param array $queryParams
     * @return bool
     */
    public function updateQueryParamsWithDefaultSorters(array &$queryParams): bool;

    /**
     * @return void
     */
    public function initFiltersWithUnpopulatedOptions();

    /**
     * @return mixed
     * @return void
     */
    public function initFilters();

    /**
     * @param array $queryParams
     * @return void
     */
    public function processQueryParams(array $queryParams);

    /**
     * @param $name
     * @return string|null
     */
    public function getFilterValueByName($name): ?string;

    /**
     * @return SearchFilter[]
     */
    public function getFilters(): array;
}