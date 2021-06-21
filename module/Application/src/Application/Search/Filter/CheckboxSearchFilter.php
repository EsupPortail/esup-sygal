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
     * @var string
     */
    protected $checkedOptionValue = '1';

    /**
     * @param array $queryParams
     * @return bool
     * @deprecated Utiliser getValue()
     */
    public function isChecked(array $queryParams): bool
    {
        $optionName = $this->getName();

        return isset($queryParams[$optionName]) && $queryParams[$optionName] === $this->checkedOptionValue;
    }

    /**
     * Adaptée pour le type Checkbox.
     *
     * @param bool $value
     * @return CheckboxSearchFilter
     */
    public function setValue($value = null): SearchFilter
    {
        return parent::setValue($value === $this->checkedOptionValue);
    }
}
