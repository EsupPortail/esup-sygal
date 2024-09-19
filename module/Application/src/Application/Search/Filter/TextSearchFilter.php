<?php

namespace Application\Search\Filter;

use Doctrine\ORM\Query\Expr\Orx;
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

    protected function applyToQueryBuilderUsingWhereFields(QueryBuilder $qb): void
    {
//        $template = $this->createTemplate("%s");
//        $paramName = uniqid('p');
//        $qb
//            ->andWhere(sprintf($template, $this->whereFields[0], $paramName))
//            ->setParameter($paramName, $this->getComparisonValue());
        $template = $this->createComparisonTemplate("%s");
        $paramName = uniqid('p');
        $or = new Orx(array_map(
            fn($field) => sprintf($template, $field, $paramName),
            $this->whereFields
        ));
        $qb
            ->andWhere($or)
            ->setParameter($paramName, $this->getComparisonValue());
    }

    protected function applyToQueryBuilderByDefault(QueryBuilder $qb): void
    {
        $alias = current($qb->getRootAliases());
        $template = $this->createComparisonTemplate("%s.%s");
        $paramName = uniqid('p');
        $qb
            ->andWhere(sprintf($template, $alias, $this->getName(), $paramName))
            ->setParameter($paramName, $this->getComparisonValue());
    }

    protected function createComparisonTemplate(string $expr): string
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

    /**
     * @param string[] $whereFields
     * @return self
     */
    public function setWhereFields(array $whereFields): self
    {
        $this->whereFields = $whereFields;

        return $this;
    }
}
