<?php

namespace Application\Search\Filter;

class WhereSearchFilter extends SearchFilter
{
    public function canApplyToQueryBuilder(): bool
    {
        return true;
    }
}
