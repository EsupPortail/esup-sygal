<?php

namespace Application\Search\Filter;

/**
 *
 *
 * @author Unicaen
 */
class CheckboxSearchFilter extends SearchFilter
{
    /**
     * @param array $queryParams
     * @return bool
     */
    public function isChecked(array $queryParams): bool
    {
        $optionName = $this->getName();
        $optionValue = '1';

        return isset($queryParams[$optionName]) && $queryParams[$optionName] === $optionValue;
    }
}
