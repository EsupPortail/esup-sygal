<?php

namespace Fichier\Search;

use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class NatureFichierSearchFilter extends SelectSearchFilter
{
    const NAME = 'nature';

    protected string $name = self::NAME;
    protected string $label = "Nature du fichier";

    public function createSorter(): SearchSorter
    {
        $sorter = new SearchSorter(
            "",
            self::NAME
        );

        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, QueryBuilder $qb) {
                $direction = $sorter->getDirection();
                $qb->addOrderBy('nf.code', $direction);
            }
        );

        return $sorter;
    }
}