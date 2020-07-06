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
    public function getName();

    /**
     * @param string $name
     * @return self
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $label
     * @return self
     */
    public function setLabel($label);

    /**
     * @return string|string[]
     */
    public function getValue();

    /**
     * @param string|null $value
     * @return self
     */
    public function setValue($value = null);

    /**
     * @return array
     */
    public function getAttributes();

    /**
     * @param array $attributes
     * @param bool $overwrite
     * @return self
     */
    public function setAttributes(array $attributes, $overwrite = false);
}
