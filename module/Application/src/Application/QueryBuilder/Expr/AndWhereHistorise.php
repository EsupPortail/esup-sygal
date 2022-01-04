<?php

namespace Application\QueryBuilder\Expr;

class AndWhereHistorise extends AndWhereExpr
{
    /**
     * AndWhereHistorise constructor.
     *
     * @param string $alias
     * @param bool $historise
     */
    public function __construct($alias, bool $historise = true)
    {
        parent::__construct($alias);

        $this->where =
            sprintf("%s.histoDestruction is ", $this->alias) .
            ($historise ? 'not null' : 'null');
    }
}