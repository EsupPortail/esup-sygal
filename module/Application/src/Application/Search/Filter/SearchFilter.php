<?php

namespace Application\Search\Filter;

use Doctrine\ORM\QueryBuilder;
use UnicaenApp\Exception\RuntimeException;

/**
 *
 *
 * @author Unicaen
 */
abstract class SearchFilter implements SearchFilterInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $value;

    /**
     * @var string
     */
    protected $defaultValue;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var callable
     */
    protected $applyToQueryBuilderCallable;

    /**
     * Constructor.
     *
     * @param string $label
     * @param string $name
     */
    public function __construct($label, $name)
    {
        $this
            ->setLabel($label)
            ->setName($name);
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
        $filterValue = $this->paramFromQueryParams($queryParams);

        $this->setValue($filterValue);
    }

    /**
     * @param array  $queryParams
     * @return string
     */
    protected function paramFromQueryParams(array $queryParams)
    {
        $name = $this->getName();

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
        $filterValue = $this->getValue();
        if (! $filterValue) {
            return;
        }

        $applyToQueryBuilder = $this->applyToQueryBuilderCallable;
        $applyToQueryBuilder($this, $qb);
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
     * @return string|string[]
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     * @return self
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string $defaultValue
     * @return self
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return self
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }
}
