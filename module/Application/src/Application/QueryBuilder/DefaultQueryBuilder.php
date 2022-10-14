<?php

namespace Application\QueryBuilder;

use Application\QueryBuilder\Expr\AndWhereExpr;
use Application\QueryBuilder\Expr\AndWhereHistorise;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * Class QueryBuilder
 *
 * @package Application\QueryBuilder
 */
class DefaultQueryBuilder extends QueryBuilder
{
    protected $rootAlias;

    /**
     * AbstractQueryBuilder constructor.
     *
     * @param EntityManagerInterface $em
     * @param null                   $rootAlias
     */
    public function __construct(EntityManagerInterface $em, $rootAlias = null)
    {
        parent::__construct($em);

        $this->rootAlias = $rootAlias ?: $this->rootAlias;
    }

    /**
     * @return self
     */
    public function initWithDefault(): self
    {
        return $this;
    }

    /**
     * @param AndWhereExpr $expr
     * @return self
     */
    protected function applyExpr(AndWhereExpr $expr): self
    {
        $expr->applyToQueryBuilder($this);

        return $this;
    }

    /**
     * @return self
     */
    public function andWhereNotHistorise(): self
    {
        return $this->applyExpr(new AndWhereHistorise($this->rootAlias, false));
    }
}