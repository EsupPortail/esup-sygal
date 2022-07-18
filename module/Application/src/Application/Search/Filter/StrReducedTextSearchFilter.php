<?php

namespace Application\Search\Filter;

use Doctrine\ORM\QueryBuilder;

class StrReducedTextSearchFilter extends TextSearchFilter
{
    protected function applyToQueryBuilderUsingWhereField(QueryBuilder $qb)
    {
        $qb
            ->andWhere(sprintf("strReduce(%s) %s strReduce(:%s)", $this->whereField, $this->getOperator(), $paramName = uniqid('p')))
            ->setParameter($paramName, $this->getComparisonValue());
    }

    protected function applyToQueryBuilderByDefault(QueryBuilder $qb)
    {
        $alias = current($qb->getRootAliases());
        $qb
            ->andWhere(sprintf("strReduce(%s.%s) %s strReduce(:%s)", $alias, $this->getName(), $this->getOperator(), $paramName = uniqid('p')))
            ->setParameter($paramName, $this->getComparisonValue());
    }
}
