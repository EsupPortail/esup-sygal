<?php

namespace Application\QueryBuilder\Expr;

use Doctrine\ORM\QueryBuilder;
use RuntimeException;

abstract class AndWhereExpr
{
    protected string $alias;
    protected string $where;
    protected array $parameters = [];

    public function __construct(string $alias)
    {
        $this->alias = $alias;
    }

    public function applyToQueryBuilder(QueryBuilder $qb): self
    {
        if (!in_array($this->alias, $qb->getAllAliases())) {
            throw new RuntimeException("L'alias $this->alias est inconnu du QueryBuilder. " . $this->getJoinSuggestion($qb->getRootAlias()));
        }

        $qb->andWhere($this->where);

        foreach ($this->parameters as $name => $value) {
            $qb->setParameter($name, $value);
        }

        return $this;
    }

    protected function getJoinSuggestion($rootAlias): string
    {
        return "";
    }
}