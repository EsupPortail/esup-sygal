<?php

namespace Application\Search\EstHistorise;

use Application\Search\Filter\CheckboxSearchFilter;
use Doctrine\ORM\QueryBuilder;

/**
 * Filtre de type case à cocher dont le fonctionnement est le suivant :
 *   - s'il est décoché : ajoute au query builder une clause écartant les enregistrements historisés ;
 *   - s'il est coché : n'ajoute rien au query builder.
 */
class EstHistoriseSearchFilter extends CheckboxSearchFilter
{
    const NAME = 'estHistorise';

    static public function newInstance(): self
    {
        return new self(
            "Historisés inclus",
            self::NAME
        );
    }

    public function applyToQueryBuilder(QueryBuilder $qb): void
    {
        $alias = current($qb->getRootAliases());

        if (!$this->getValue()) {
            $qb->andWhere("$alias.histoDestruction is null");
        }
    }
}