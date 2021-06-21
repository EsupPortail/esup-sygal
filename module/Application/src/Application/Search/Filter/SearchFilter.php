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
     * @var null|bool|string|array
     */
    protected $value;

    /**
     * @var string|bool
     */
    protected $defaultValue;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var bool
     */
    protected $visible = true;

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
    public function setQueryBuilderApplier(callable $applyToQueryBuilderCallable): self
    {
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
     * Application de ce filtre au query builder spécifié.
     *
     * Cette méthode appelle le callable spécifié via {@see setQueryBuilderApplier()}.
     * Mais elle peut être redéfinie dans les classes filles.
     *
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        if ($this->applyToQueryBuilderCallable === null) {
            throw new RuntimeException("Aucun callable spécifié pour le filtre suivant : " . $this->name);
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
     * @return null|bool|string|array
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string|bool|null $value
     * @return self
     */
    public function setValue($value = null): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string|bool
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string|bool|null $defaultValue
     * @return self
     */
    public function setDefaultValue($defaultValue = null): self
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

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     * @return self
     */
    public function setVisible(bool $visible = true): self
    {
        $this->visible = $visible;
        return $this;
    }
}
