<?php

namespace Admission\Search\Admission;

use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class EtatAdmissionSearchFilter extends SelectSearchFilter
{
    const NAME = 'etat';

    protected string $name = self::NAME;
    protected string $label = "État";

    public function applyToQueryBuilder(QueryBuilder $qb): void
    {
        $alias = 'admission'; // todo: rendre paramétrable

        $qb
            ->andWhere("$alias.etat = :etat")
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
            function (SearchSorter $sorter, QueryBuilder $qb, $alias = 'admission') {
                $direction = $sorter->getDirection();
                $qb
                    ->addOrderBy("$alias.etat", $direction);
            }
        );

        return $sorter;
    }
}