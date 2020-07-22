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
    private $label;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $enabled = false;

    /**
     * @var string
     */
    private $direction = Sortable::ASC;

    /**
     * @var bool
     */
    private $isDefault = false;

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
    public function __construct($label, $name, $isDefault = false)
    {
        $this
            ->setLabel($label)
            ->setName($name)
            ->setIsDefault($isDefault);
    }

    /**
     * @param callable $applyToQueryBuilderCallable
     * @return self
     */
    public function setApplyToQueryBuilderCallable($applyToQueryBuilderCallable)
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
    private function paramFromQueryParams($name, array $queryParams)
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

        $applyToQueryBuilder = $this->applyToQueryBuilderCallable;
        $applyToQueryBuilder($this, $qb);
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string $direction
     * @return self
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return $this->isDefault;
    }

    /**
     * @param bool $isDefault
     * @return self
     */
    public function setIsDefault($isDefault = true)
    {
        $this->isDefault = $isDefault;

        return $this;
    }
}
