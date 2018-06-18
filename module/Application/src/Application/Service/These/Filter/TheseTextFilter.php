<?php

namespace Application\Service\These\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 *
 *
 * @author Unicaen
 */
class TheseTextFilter extends TheseFilter
{
    const NAME_text = 'text';

    /**
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        // not possible
    }
}
