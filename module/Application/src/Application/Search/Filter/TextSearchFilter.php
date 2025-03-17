<?php

namespace Application\Search\Filter;

class TextSearchFilter extends SearchFilter
{
    protected bool $useStrReduce = false;

    /**
     * Active ou désactive l'utilisation de la fonction PgSQL `strReduce()` sur les membres de la comparaison.
     */
    public function useStrReduce(bool $useStrReduce = true): static
    {
        $this->useStrReduce = $useStrReduce;
        return $this;
    }

    protected bool $useLikeOperator = false;

    /**
     * Actve ou désactive l'utilisation de l'opérateur `like` pour la comparaison.
     */
    public function useLikeOperator(bool $useLikeOperator = true): static
    {
        $this->useLikeOperator = $useLikeOperator;
        return $this;
    }

    public function canApplyToQueryBuilder(): bool
    {
        $filterValue = trim($this->getValue());

        return strlen($filterValue) > 1;
    }

//    protected function applyToQueryBuilderUsingWhereFields(QueryBuilder $qb): void
//    {
//        $template = $this->generateComparisonTemplate("%s");
//        $paramName = uniqid('p');
//        $qb
//            ->andWhere(sprintf($template, $this->whereField, $paramName))
//            ->setParameter($paramName, $this->generateComparisonValue());
//    }
//
//    protected function applyToQueryBuilderByDefault(QueryBuilder $qb): void
//    {
//        $alias = current($qb->getRootAliases());
//        $template = $this->generateComparisonTemplate("%s.%s");
//        $paramName = uniqid('p');
//        $qb
//            ->andWhere(sprintf($template, $alias, $this->getName(), $paramName))
//            ->setParameter($paramName, $this->generateComparisonValue());
//    }

    protected function generateComparisonTemplate(string $operand): string
    {
        $leftOperand = $operand;
        $rigthOperand = ':%s';
        $operator = ' = ';

        if ($this->useStrReduce) {
            $leftOperand = "strReduce($leftOperand)";
            $rigthOperand = "strReduce($rigthOperand)";
        }
        if ($this->useLikeOperator) {
            $leftOperand = "lower($leftOperand)";
            $rigthOperand = "lower($rigthOperand)";
            $operator = ' like ';
        }

        return $leftOperand . $operator . $rigthOperand;
    }

    public function generateComparisonValue(): string
    {
        $filterValue = trim($this->getValue());

        return $this->useLikeOperator ? "%$filterValue%" : $filterValue;
    }
}
