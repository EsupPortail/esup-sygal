<?php

namespace Application\Search\Financement;

use Application\Search\Filter\SelectSearchFilter;
use Doctrine\ORM\QueryBuilder;

class AnneeFinancementSearchFilter extends SelectSearchFilter
{
    const NAME = 'financementAnnee';

    protected string $name = self::NAME;
    protected string $label = "Année financ.";

    public function applyToQueryBuilder(QueryBuilder $qb): void
    {
        $alias = 'these'; // todo: rendre paramétrable

        $filterValue = $this->getValue();

        $qb
            ->join("$alias.financements", $aliasFinanc = uniqid('financ_'))
            ->andWhere("$aliasFinanc.annee = :anneeFinanc")
            ->andWhere("$aliasFinanc.histoDestruction is null")
            ->setParameter('anneeFinanc', $filterValue);
    }
}