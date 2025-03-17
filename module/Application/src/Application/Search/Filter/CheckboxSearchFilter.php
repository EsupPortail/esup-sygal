<?php

namespace Application\Search\Filter;

class CheckboxSearchFilter extends SearchFilter
{
    protected string $checkedOptionValue = '1';

    public function canApplyToQueryBuilder(): bool
    {
        return true;
    }

    public function setValue($value = null): static
    {
        return parent::setValue($value === $this->checkedOptionValue);
    }

    public function generateComparisonValue(): bool
    {
        return (bool)$this->getValue();
    }
}
