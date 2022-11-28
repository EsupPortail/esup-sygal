<?php

namespace Application\QueryBuilder\Expr;

class AndWhereHistorise extends AndWhereExpr
{
    public function __construct(string $alias, bool $historise = true)
    {
        parent::__construct($alias);

        $this->where =
            sprintf("%s.histoDestruction is ", $this->alias) .
            ($historise ? 'not null' : 'null');
    }
}