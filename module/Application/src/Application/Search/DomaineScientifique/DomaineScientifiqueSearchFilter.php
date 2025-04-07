<?php

namespace Application\Search\DomaineScientifique;

use Application\Search\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;

class DomaineScientifiqueSearchFilter extends SelectSearchFilter
{
    public const NAME = 'domaineScientifique';

    protected string $name = self::NAME;
    protected string $label = "Dom. scien.";
    protected array $attributes = ['liveSearch' => true];
    protected string $emptyOptionLabel = "Tous";

    public function applyToQueryBuilder(QueryBuilder $qb): void
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