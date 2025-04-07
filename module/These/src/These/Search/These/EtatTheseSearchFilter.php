<?php

namespace These\Search\These;

use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class EtatTheseSearchFilter extends SelectSearchFilter
{
    public const NAME = 'etatThese';

    protected string $name = self::NAME;
    protected string $label = "État";

    public function applyToQueryBuilder(QueryBuilder $qb): void
    {
        $alias = 'these'; // todo: rendre paramétrable

        $qb
            ->andWhere("$alias.etatThese = :etat")
            ->setParameter('etat', $this->getValue());
    }

    /**
     * @return SearchSorter
     */
    public function createSorter(): SearchSorter
    {
        $sorter = new SearchSorter(
            "",
            self::NAME
        );

        $sorter->setQueryBuilderApplier(
            function (SearchSorter $sorter, QueryBuilder $qb, $alias = 'these') {
                $direction = $sorter->getDirection();
                $qb
                    ->addOrderBy("$alias.etatThese", $direction);
            }
        );

        return $sorter;
    }
}