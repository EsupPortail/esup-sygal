<?php

namespace Application\Search\Filter;

use Doctrine\ORM\QueryBuilder;

/**
 *
 *
 * @author Unicaen
 */
class TextSearchFilter extends SearchFilter
{
    protected bool $useLikeOperator = false;

    /**
     * @param bool $useLikeOperator
     * @return self
     */
    public function setUseLikeOperator(?bool $useLikeOperator = true): self
    {
        $this->useLikeOperator = $useLikeOperator;
        return $this;
    }

    protected function canApplyToQueryBuilder(): bool
    {
        $filterValue = $this->getValue();

        return $filterValue !== null && strlen($filterValue) > 1;
    }

    protected function applyToQueryBuilderUsingWhereField(QueryBuilder $qb)
    {
        $qb
            ->andWhere(sprintf("%s %s :%s", $this->whereField, $this->getOperator(), $paramName = uniqid('p')))
            ->setParameter($paramName, $this->getComparisonValue());
    }

    protected function applyToQueryBuilderByDefault(QueryBuilder $qb)
    {
        $alias = current($qb->getRootAliases());
        $qb
            ->andWhere(sprintf("%s.%s %s :%s", $alias, $this->getName(), $this->getOperator(), $paramName = uniqid('p')))
            ->setParameter($paramName, $this->getComparisonValue());
    }

    protected function getOperator(): string
    {
        return $this->useLikeOperator ? 'LIKE' : '=';
    }

    public function getComparisonValue(): string
    {
        $filterValue = $this->getValue();

        return $this->useLikeOperator ? "%$filterValue%" : $filterValue;
    }
}
