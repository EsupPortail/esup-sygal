<?php

namespace Application\Search;

use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SearchFilterPluginManager;
use Application\Search\Filter\SearchFilterPluginManagerAwareInterface;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

/**
 * Classe mère des services à injecter dans un {@see \Application\Search\Controller\SearchControllerInterface} et
 * fournissant le nécessaire pour gérer une page de recherche/filtrage de données à l'aide de filtres normalisés de
 * type {@see \Application\Search\Filter\SearchFilterInterface}.
 */
abstract class SearchService implements SearchServiceInterface, SearchFilterPluginManagerAwareInterface
{
    protected bool $unpopulatedOptions = false;

    /**
     * @var SearchFilter[]
     */
    protected array $filters = [];

    /**
     * @var SearchSorter[]
     */
    protected array $sorters = [];

    /**
     * @var SearchSorter[]
     */
    protected array $invisibleSorters = [];

    protected ?SearchSorter $defaultSorter = null;

    protected SearchFilterPluginManager $searchFilterPluginManager;


    public function setSearchFilterPluginManager(SearchFilterPluginManager $searchFilterPluginManager): void
    {
        $this->searchFilterPluginManager = $searchFilterPluginManager;
    }

    abstract public function init(): void;

    /**
     * Ajoute un filtre.
     */
    public function addFilter(SearchFilter $filter): static
    {
        $this->filters[$filter->getName()] = $filter;

        return $this;
    }

    /**
     * Ajoute plusieurs filtres.
     *
     * @param SearchFilter[] $filters
     */
    public function addFilters(array $filters): static
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }

        return $this;
    }

    /**
     * Ajoute un tri.
     *
     * @param SearchSorter $sorter
     */
    public function addSorter(SearchSorter $sorter): static
    {
        $this->sorters[$sorter->getName()] = $sorter;

        if ($sorter->isDefault()) {
            $this->defaultSorter = $sorter;
        }

        return $this;
    }

    /**
     * Ajoute plusieurs tris.
     *
     * @param SearchSorter[] $sorters
     */
    public function addSorters(array $sorters): static
    {
        foreach ($sorters as $sorter) {
            $this->addSorter($sorter);
        }

        return $this;
    }

    /**
     * Ajoute un tri invisible à appliquer systématiquement et en dernier.
     */
    public function addInvisibleSort(string $sort, ?string $order = null): static
    {
        $sorter = new SearchSorter('Final', uniqid('final_'));
        $sorter->setQueryBuilderApplier(function (SearchSorter $sorter, QueryBuilder $qb) use ($sort, $order) {
            $qb->addOrderBy($sort, $order);
        });

        return $this->addInvisibleSorter($sorter);
    }

    /**
     * Ajoute un {@see SearchSorter} invisible à appliquer systématiquement et en dernier.
     *
     * @param SearchSorter $sorter
     */
    public function addInvisibleSorter(SearchSorter $sorter): static
    {
        $sorter->setEnabled(true);

        $this->invisibleSorters[] = $sorter;

        return $this;
    }

    /**
     * Retourne le filtre dont le nom est spécifié.
     *
     * @param string $name
     * @return SearchFilter
     */
    public function getFilterByName(string $name): SearchFilter
    {
        return $this->filters[$name];
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function initFiltersAsEmpty(): void
    {
        $emptyValueOptions = array_map(fn($filter) => [], $this->filters);

        $this->initFiltersValueOptions($emptyValueOptions);

        $this->unpopulatedOptions = true;
    }

    public function initFilters(): void
    {
        $filterValueOptions = [];
        foreach ($this->filters as $filterName => $filter) {
            $filter->init();
            if ($filter instanceof SelectSearchFilter) {
                // si des valeurs ont déjà été fournies, pas besoin de fetch.
                if (($data = $filter->getData()) === null) {
                    $data = $filter->fetchData(); // obtention des données
                }
                $valueOptions = $filter->createValueOptionsFromData($data);
                $filterValueOptions[$filterName] = $valueOptions;
            } else {
                $filterValueOptions[$filterName] = [];
            }
        }

        $this->initFiltersValueOptions($filterValueOptions);

        $this->unpopulatedOptions = false;
    }

    protected function initFiltersValueOptions(array $valueOptions): void
    {
        foreach ($this->filters as $filterName => $filter) {
            //$filter->setAttributes([]);
            if ($filter instanceof SelectSearchFilter) {
                $filter->setValueOptions($valueOptions[$filterName] ?? []);
            }
        }
    }

    public function processQueryParams(array $queryParams): void
    {
        foreach ($this->filters as $filter) {
            $filter->processQueryParams($queryParams);
        }
        foreach ($this->sorters as $sorter) {
            $sorter->processQueryParams($queryParams);
        }
    }

    public function getFilterValueByName(string $name): mixed
    {
        if (isset($this->filters[$name])) {
            return $this->filters[$name]->getValue();
        }

        return null;
    }

    public function updateQueryParamsWithDefaultFilterValues(array &$queryParams): bool
    {
        $updated = false;

        foreach ($this->filters as $filterName => $filter) {
            $defaultValue = $filter->getDefaultValue();
            if ($defaultValue === null) {
                continue;
            }
            //$queryParamValue = $this->paramFromQueryParams($filterName, $queryParams); // NB: null <=> filtre absent
            $queryParamValue = $filter->paramFromQueryParams($queryParams); // NB: null <=> filtre absent
            if ($queryParamValue === null) {
                $queryParams = array_merge($queryParams, [$filterName => $defaultValue]);
                $updated = true;
            }
        }

        return $updated;
    }

    public function updateQueryParamsWithDefaultSorters(array &$queryParams): bool
    {
        if ($this->defaultSorter === null) {
            return false;
        }

        $updated = false;

//        $sort = $this->paramFromQueryParams('sort', $queryParams);
        $sort = array_key_exists('sort', $queryParams) ? $queryParams['sort'] : null;

        // Si aucun tri n'est présent, on trie par date de 1ere inscription
        if ($sort === null || $sort === '') {
            $queryParams = array_merge($queryParams, [
                'sort' => $this->defaultSorter->getName(),
                'direction' => $this->defaultSorter->getDirection(),
            ]);
            $updated = true;
        }

        return $updated;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder();

        foreach ($this->filters as $filter) {
            $filter->applyDefaultsToQueryBuilder($qb);
            if ($filter->canApplyToQueryBuilder()) {
                $filter->applyToQueryBuilder($qb);
            }
        }

        foreach ($this->sorters as $sorter) {
            $sorter->applyToQueryBuilder($qb);
        }
        foreach ($this->invisibleSorters as $sorter) {
            $sorter->applyToQueryBuilder($qb);
        }

        return $qb;
    }

    abstract protected function createQueryBuilder(): QueryBuilder;
}
