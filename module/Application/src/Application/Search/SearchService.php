<?php

namespace Application\Search;

use Application\Search\Filter\SearchFilter;
use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;

abstract class SearchService implements SearchServiceInterface
{
    /**
     * @var bool
     */
    protected $unpopulatedOptions = false;

    /**
     * @var SearchFilter[]
     */
    protected $filters = [];

    /**
     * @var SearchSorter[]
     */
    protected $sorters = [];

    /**
     * @var SearchSorter[]
     */
    protected array $invisibleSorters = [];

    /**
     * @var SearchSorter
     */
    protected $defaultSorter;

    /**
     * @inheritDoc
     */
    abstract public function init();

    /**
     * @param SearchFilter $filter
     * @return self
     */
    public function addFilter(SearchFilter $filter): self
    {
        $this->filters[$filter->getName()] = $filter;

        return $this;
    }

    /**
     * @param SearchFilter[] $filters
     * @return self
     */
    public function addFilters(array $filters): self
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }

        return $this;
    }

    /**
     * @param string $label
     * @param string $name
     * @param string $sortExpr
     * @param bool $isDefault
     * @return self
     */
    public function addSort(string $label, string $name, string $sortExpr, bool $isDefault = false): self
    {
        $sorter = new SearchSorter($label, $name, $isDefault);
        $sorter->setQueryBuilderApplier(function (SearchSorter $sorter, QueryBuilder $qb) use ($sortExpr) {
            $qb->addOrderBy($sortExpr);
        });

        return $this->addSorter($sorter);
    }

    /**
     * @param SearchSorter $sorter
     * @return self
     */
    public function addSorter(SearchSorter $sorter): self
    {
        $this->sorters[$sorter->getName()] = $sorter;

        if ($sorter->isDefault()) {
            $this->defaultSorter = $sorter;
        }

        return $this;
    }

    /**
     * @param SearchSorter[] $sorters
     * @return self
     */
    public function addSorters(array $sorters): self
    {
        foreach ($sorters as $sorter) {
            $this->addSorter($sorter);
        }

        return $this;
    }

    /**
     * Ajoute un tri invisible à appliquer systématiquement et en dernier.
     *
     * @param string $sort
     * @param string|null $order
     * @return self
     */
    public function addInvisibleSort(string $sort, ?string $order = null): self
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
     * @return self
     */
    public function addInvisibleSorter(SearchSorter $sorter): self
    {
        $sorter->setEnabled(true);

        $this->invisibleSorters[] = $sorter;

        return $this;
    }

    /**
     * Ajoute des {@see SearchSorter} invisibles à appliquer systématiquement et en dernier, dans l'ordre.
     *
     * @param SearchSorter[] $sorters
     * @return self
     */
    public function addInvisibleSorters(array $sorters): self
    {
        foreach ($sorters as $sorter) {
            $this->addInvisibleSorter($sorter);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function initFiltersWithUnpopulatedOptions()
    {
        $filterValueOptions = [];
        foreach ($this->filters as $filterName => $filter) {
            $filterValueOptions[$filterName] = [];
        }

        $this->initFiltersArray($filterValueOptions);

        $this->unpopulatedOptions = true;
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

    /**
     * Retourne la liste des filtres.
     *
     * @return SearchFilter[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @inheritDoc
     */
    public function initFilters()
    {
        $filterValueOptions = [];
        foreach ($this->filters as $filterName => $filter) {
            $filter->init();
            if ($filter instanceof SelectSearchFilter) {
                // si des valeurs ont déjà été fournies, pas besoin de fetch.
                if (($data = $filter->getData()) === null) {
                    if ($dataProvider = $filter->getDataProvider()) {
                        $data = $dataProvider($filter); // obtention des données
                    } else {
                        throw new RuntimeException("Aucun 'data provider' fourni pour le filtre '$filterName'");
                    }
                }
                $valueOptions = $filter->createValueOptionsFromData($data);
                $filterValueOptions[$filterName] = $valueOptions;
            } else {
                $filterValueOptions[$filterName] = [];
            }
        }

        $this->initFiltersArray($filterValueOptions);

        $this->unpopulatedOptions = false;
    }

    /**
     * @param array $valueOptions
     * @param array $attributes
     */
    protected function initFiltersArray(array $valueOptions, array $attributes = [])
    {
        foreach ($this->filters as $filterName => $filter) {
            $filter->setAttributes($attributes[$filterName] ?? []);
            if ($filter instanceof SelectSearchFilter) {
                $filter->setOptions($valueOptions[$filterName] ?? []);
            }
        }
    }

    /**
     * @param array $queryParams
     */
    public function processQueryParams(array $queryParams)
    {
        foreach ($this->filters as $filter) {
            $filter->processQueryParams($queryParams);
        }
        foreach ($this->sorters as $sorter) {
            $sorter->processQueryParams($queryParams);
        }
    }

    /**
     * @param $name
     * @return string|null
     */
    public function getFilterValueByName($name): ?string
    {
        if (isset($this->filters[$name])) {
            return $this->filters[$name]->getValue();
        }

        return null;
    }

    /**
     * @param string $name
     * @param array $queryParams
     * @return string
     */
    private function paramFromQueryParams(string $name, array $queryParams): ?string
    {
        if (! array_key_exists($name, $queryParams)) {
            // null <=> paramètre absent
            return null;
        }

        // NB: '' <=> "Tous"

        return $queryParams[$name];
    }

    /**
     * @param array $queryParams
     * @return bool
     */
    public function updateQueryParamsWithDefaultFilters(array &$queryParams): bool
    {
        $updated = false;

        foreach ($this->filters as $filterName => $filter) {
            $defaultValue = $filter->getDefaultValue();
            if ($defaultValue === null) {
                continue;
            }
            $queryParamValue = $this->paramFromQueryParams($filterName, $queryParams); // NB: null <=> filtre absent
            if ($queryParamValue === null) {
                $queryParams = array_merge($queryParams, [$filterName => $defaultValue]);
                $updated = true;
            }
        }

        return $updated;
    }

    /**
     * @param array $queryParams
     * @return bool
     */
    public function updateQueryParamsWithDefaultSorters(array &$queryParams): bool
    {
        if ($this->defaultSorter === null) {
            return false;
        }

        $updated = false;

        $sort = $this->paramFromQueryParams('sort', $queryParams);

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

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        $qb = $this->createQueryBuilder();

        foreach ($this->filters as $filter) {
            if ($filter->getValue()) {
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

    /**
     * @return QueryBuilder
     */
    abstract protected function createQueryBuilder(): QueryBuilder;
}
