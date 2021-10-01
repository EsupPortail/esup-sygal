<?php

namespace Application\Search\Financement;

use Application\Search\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;

class OrigineFinancementSearchFilter extends SelectSearchFilter
{
    const NAME = 'financement';

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
        $instance = new self(
            "Orig. financ.",
            self::NAME,
            ['liveSearch' => true]
        );

        return $instance;
    }

    /**
     * @param QueryBuilder $qb
     */
    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        $alias = 'these'; // todo: rendre paramétrable

        $filterValue = $this->getValue();

        $qb
            ->leftJoin("$alias.financements", 'fin')
            ->leftJoin('fin.origineFinancement', 'orig')
        ;
        if ($filterValue === 'NULL') {
            $qb
                ->andWhere('orig.id IS NULL');
        } else {
            $qb
                ->andWhere('orig.code = :origine')
                ->setParameter('origine', $filterValue);
        }
    }
}