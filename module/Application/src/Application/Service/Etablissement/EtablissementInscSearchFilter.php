<?php

namespace Application\Service\Etablissement;

use Application\Search\Filter\SelectSearchFilter;
use Application\Search\Sorter\SearchSorter;
use Doctrine\ORM\QueryBuilder;

class EtablissementInscSearchFilter extends SelectSearchFilter
{
    const NAME = 'etab_inscription';

    /**
     * @inheritDoc
     */
    protected function __construct(string $label, string $name, array $options, array $attributes = [], $defaultValue = null)
    {
        parent::__construct($label, $name, $options, $attributes, $defaultValue);
    }

    /**
     * @return self
     */
    static public function newInstance(): self
    {
        return new self(
            "Établissement<br>d'inscr.",
            self::NAME,
            []
        );
    }

    /**
     * @inheritDoc
     */
    public function createValueOptionsFromData(array $data): array
    {
        $options = [];
        $options[] = $this->valueOptionEmpty();
        foreach ($data as $etablissement) {
            $options[] = $this->valueOptionEntity($etablissement);
        }

        return $options;
    }

    /**
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        $alias = 'these'; // todo: rendre paramétrable

        $filterValue = $this->getValue();
        if (!$filterValue) {
            return;
        }

        $qb
            ->andWhere("$alias.etablissement = :etab")
            ->setParameter('etab', $filterValue);
    }

    /**
     * @return SearchSorter
     */
    public function createSorter(): SearchSorter
    {
        $sorter = new SearchSorter(
            "Établissement<br>d'inscr.",
            self::NAME
        );

        $sorter->setApplyToQueryBuilderCallable(
            function (SearchSorter $sorter, QueryBuilder $qb, $alias = 'these') {
                $direction = $sorter->getDirection();
                $qb
                    ->join("$alias.etablissement", 'e_sort')
                    ->join('e_sort.structure', 's_sort')
                    ->addOrderBy('s_sort.code', $direction);
            }
        );

        return $sorter;
    }
}