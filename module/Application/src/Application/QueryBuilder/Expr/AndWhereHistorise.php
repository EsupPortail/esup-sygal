<?php

namespace Application\QueryBuilder\Expr;

class AndWhereHistorise extends AndWhereExpr
{
    /**
     * AndWhereHistorise constructor.
     *
     * @param string $alias
     * @param bool   $historise
     */
    public function __construct($alias, $historise = true)
    {
        parent::__construct($alias);

        $this->where = sprintf("%s = pasHistorise(%s)", $historise ? 0 : 1, $this->alias);
    }
}