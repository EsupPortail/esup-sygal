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
    protected string $likeOperator = 'LIKE';

    /**
     * @param bool $useLikeOperator
     * @return self
     */
    public function setUseLikeOperator(?bool $useLikeOperator = true): self
    {
        $this->useLikeOperator = $useLikeOperator;
        return $this;
    }

    /**
     * @param string $likeOperator
     * @return self
     */
    public function setLikeOperator(string $likeOperator): self
    {
        $this->likeOperator = $likeOperator;
        return $this;
    }

    public function canApplyToQueryBuilder(): bool
    {
        $filterValue = trim($this->getValue());

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
        return $this->useLikeOperator ? $this->likeOperator : '=';
    }

    public function getComparisonValue(): string
    {
        $filterValue = trim($this->getValue());

        return $this->useLikeOperator ? "%$filterValue%" : $filterValue;
    }
}
