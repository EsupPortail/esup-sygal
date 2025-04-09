<?php

namespace HDR\Search\HDR;

use UnicaenSearch\Filter\SelectSearchFilter;
use UnicaenSearch\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class EtatHDRSearchFilter extends SelectSearchFilter
{
    public const NAME = 'etatHDR';

    /**
     * @inheritDoc
     */
    protected function __construct(string $label, string $name, array $attributes = [], $defaultValue = null)
    {
        parent::__construct($label, $name, $attributes, $defaultValue);
    }

    /**
     * @return self
     */
    static public function newInstance(): self
    {
        return new self(
            "État",
            self::NAME
        );
    }

    /**
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb): void
    {
        $alias = 'hdr'; // todo: rendre paramétrable

        $qb
            ->andWhere("$alias.etatHDR = :etat")
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
            function (SearchSorter $sorter, QueryBuilder $qb, $alias = 'hdr') {
                $direction = $sorter->getDirection();
                $qb
                    ->addOrderBy("$alias.etatHDR", $direction);
            }
        );

        return $sorter;
    }
}