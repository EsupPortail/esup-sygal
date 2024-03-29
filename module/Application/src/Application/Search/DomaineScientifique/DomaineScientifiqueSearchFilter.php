<?php

namespace Application\Search\DomaineScientifique;

use Application\Search\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;

class DomaineScientifiqueSearchFilter extends SelectSearchFilter
{
    const NAME = 'domaineScientifique';

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
            "Dom. scien.",
            self::NAME,
            ['liveSearch' => true]
        );

        $instance->setEmptyOptionLabel("Tous");

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
            ->leftJoin("$alias.uniteRecherche", 'ur2')
            ->leftJoin('ur2.domaines', 'dom')
        ;
        if ($filterValue === 'NULL') {
            $qb
                ->andWhere('dom.id IS NULL');
        } else {
            $qb
                ->andWhere('dom.id = :domaine')
                ->setParameter('domaine', $filterValue);
        }
    }
}