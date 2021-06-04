<?php

namespace Application\Search\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 *
 *
 * @author Unicaen
 */
interface SearchFilterInterface
{
    /**
     * @param array $queryParams
     */
    public function processQueryParams(array $queryParams);

    /**
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getLabel(): string;

    /**
     * @param string $label
     */
    public function setLabel(string $label);

    /**
     * @return null|string|array
     */
    public function getValue();

    /**
     * @param string|null $value
     */
    public function setValue(string $value = null);

    /**
     * @return array
     */
    public function getAttributes(): array;

    /**
     * @param array $attributes
     * @param bool $overwrite
     */
    public function setAttributes(array $attributes, $overwrite = false);
}
