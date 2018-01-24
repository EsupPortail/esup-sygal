<?php

namespace Application\QueryBuilder\Expr;

use Doctrine\ORM\QueryBuilder;

abstract class AndWhereExpr
{
    protected $alias;
    protected $where;
    protected $parameters;

    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    public function applyToQueryBuilder(QueryBuilder $qb)
    {
        if (!in_array($this->alias, $qb->getAllAliases())) {
            throw new \RuntimeException("L'alias $this->alias est inconnu du QueryBuilder. " . $this->getJoinSuggestion($qb->getRootAlias()));
        }

        $qb->andWhere($this->where);

        foreach ((array)$this->parameters as $name => $value) {
            $qb->setParameter($name, $value);
        }

        return $this;
    }

    protected function getJoinSuggestion($rootAlias)
    {
        return "";
    }
}