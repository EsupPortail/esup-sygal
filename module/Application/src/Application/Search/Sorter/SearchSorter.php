<?php

namespace Application\Search\Sorter;

use Application\View\Helper\Sortable;
use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;

/**
 *
 *
 * @author Unicaen
 */
class SearchSorter
{
    /**
     * @var string
     */
    private string $label;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var bool
     */
    private bool $enabled = false;

    /**
     * @var string
     */
    private string $direction = Sortable::ASC;

    /**
     * @var bool
     */
    private bool $isDefault = false;

    /**
     * @var string|null
     */
    private ?string $orderByField = null;

    /**
     * @var callable
     */
    protected $applyToQueryBuilderCallable;

    /**
     * SelectFilter constructor.
     *
     * @param string $label
     * @param string $name
     * @param bool $isDefault
     */
    public function __construct(string $label, string $name, bool $isDefault = false)
    {
        $this
            ->setLabel($label)
            ->setName($name)
            ->setIsDefault($isDefault);
    }

    /**
     * Spécifie le callable qui sera appelé pour appliquer le filtre au query builder.
     *
     * @param callable $applyToQueryBuilderCallable function(SearchFilter $filter, QueryBuilder $qb)
     * @return self
     */
    public function setQueryBuilderApplier(callable $applyToQueryBuilderCallable): self
    {
        if (! is_callable($applyToQueryBuilderCallable)) {
            throw new RuntimeException("Callable spécifié invalide !");
        }

        $this->applyToQueryBuilderCallable = $applyToQueryBuilderCallable;

        return $this;
    }

    /**
     * @param array $queryParams
     */
    public function processQueryParams(array $queryParams)
    {
        $sortQueryParam = $this->paramFromQueryParams('sort', $queryParams) ? $queryParams['sort'] : null;
        if (! $sortQueryParam) {
            $this->setEnabled(false);
            return;
        }

        // NB: le tri ne peut porter que sur un seul attribut

        if ($sortQueryParam !== $this->getName()) {
            $this->setEnabled(false);
            return;
        }

        $direction = $this->paramFromQueryParams('direction', $queryParams) ?: Sortable::ASC;

        $this->setEnabled(true);
        $this->setDirection($direction);
    }

    /**
     * @param string $name
     * @param array  $queryParams
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
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        if (! $this->isEnabled()) {
            return;
        }

        if ($this->applyToQueryBuilderCallable === null) {
            if ($this->orderByField !== null) {
                // utilisation du champ de tri spécifié
                $this->applyToQueryBuilderCallable = function (SearchSorter $sorter, QueryBuilder $qb) {
                    $qb->addOrderBy($this->orderByField, $sorter->getDirection());
                };
            } else {
                // tentative de construction automatique du tri
                $this->applyToQueryBuilderCallable = function (SearchSorter $sorter, QueryBuilder $qb) {
                    $alias = current($qb->getRootAliases());
                    $qb->addOrderBy(sprintf("%s.%s", $alias, $sorter->getName()), $sorter->getDirection());
                };
            }
        }

        $applyToQueryBuilder = $this->applyToQueryBuilderCallable;
        $applyToQueryBuilder($this, $qb);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return self
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName(string $name): SearchSorter
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return self
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     * @return self
     */
    public function setDirection(string $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     * @return self
     */
    public function setIsDefault(bool $isDefault = true): self
    {
        $this->isDefault = $isDefault;

        return $this;
    }

    /**
     * @param string $orderByField
     * @return self
     */
    public function setOrderByField(string $orderByField): self
    {
        $this->orderByField = $orderByField;

        return $this;
    }
}
