<?php

namespace Application\Search\Filter;

use Doctrine\ORM\QueryBuilder;

class TextSearchFilter extends SearchFilter
{
    protected bool $useLikeOperator = false;

    public function useLikeOperator(bool $useLikeOperator = true): self
    {
        $this->useLikeOperator = $useLikeOperator;
        return $this;
    }

    public function canApplyToQueryBuilder(): bool
    {
        $filterValue = trim($this->getValue());

        return strlen($filterValue) > 1;
    }

    protected function applyToQueryBuilderUsingWhereField(QueryBuilder $qb): void
    {
        $template = $this->createTemplate("%s");
        $paramName = uniqid('p');
        $qb
            ->andWhere(sprintf($template, $this->whereField, $paramName))
            ->setParameter($paramName, $this->getComparisonValue());
    }

    protected function applyToQueryBuilderByDefault(QueryBuilder $qb): void
    {
        $alias = current($qb->getRootAliases());
        $template = $this->createTemplate("%s.%s");
        $paramName = uniqid('p');
        $qb
            ->andWhere(sprintf($template, $alias, $this->getName(), $paramName))
            ->setParameter($paramName, $this->getComparisonValue());
    }

    protected function createTemplate(string $expr): string
    {
        if ($this->useLikeOperator) {
            $template = "lower($expr) like lower(:%s)";
        } else {
            $template = "$expr = :%s";
        }

        return $template;
    }

    public function getComparisonValue(): string
    {
        $filterValue = trim($this->getValue());

        return $this->useLikeOperator ? "%$filterValue%" : $filterValue;
    }
}
