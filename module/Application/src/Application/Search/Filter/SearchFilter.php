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
    public function __construct(string $label, string $name)
    {
        $this
            ->setLabel($label)
            ->setName($name);
    }

    /**
     * @return self
     */
    public function init(): self
    {
        // initialisations utiles
        // ...

        return $this;
    }

    /**
     * @param callable $applyToQueryBuilderCallable
     * @return self
     */
    public function setApplyToQueryBuilderCallable(callable $applyToQueryBuilderCallable): self
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
    protected function paramFromQueryParams(array $queryParams): ?string
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

        if ($this->applyToQueryBuilderCallable === null) {
            throw new RuntimeException("Aucun callable spécifié");
        }

        $applyToQueryBuilder = $this->applyToQueryBuilderCallable;
        $applyToQueryBuilder($this, $qb);
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
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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
    public function setValue($value = null): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    /**
     * @param string|null $defaultValue
     * @return self
     */
    public function setDefaultValue(string $defaultValue = null): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @param bool $overwrite
     * @return self
     */
    public function setAttributes(array $attributes, $overwrite = false): self
    {
        $this->attributes = $overwrite ? $attributes : array_merge($this->attributes, $attributes);

        return $this;
    }
}
