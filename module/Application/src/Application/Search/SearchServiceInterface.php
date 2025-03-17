<?php

namespace Application\Search;

use Application\Search\Filter\SearchFilter;

interface SearchServiceInterface
{
    /**
     * Initialisation nécessaires, ex: ajouts des filtres, des trieurs.
     * @return void
     */
    public function init(): void;

    /**
     * @param array $queryParams
     * @return bool
     */
    public function updateQueryParamsWithDefaultFilterValues(array &$queryParams): bool;

    /**
     * @param array $queryParams
     * @return bool
     */
    public function updateQueryParamsWithDefaultSorters(array &$queryParams): bool;

    /**
     * Intialise les filtres
     */
    public function initFiltersAsEmpty(): void;

    /**
     * Initialise les filtres.
     */
    public function initFilters(): void;

    /**
     * Applique ou exploite les 'query params' spécifiés, pour valuer les filtres, les tris, etc.
     */
    public function processQueryParams(array $queryParams): void;

    /**
     * Retourne la valeur actuelle du filtre spécifié par son 'name'.
     */
    public function getFilterValueByName(string $name): mixed;

    /**
     * Retourne la liste des filtres.
     *
     * @return SearchFilter[]
     */
    public function getFilters(): array;
}