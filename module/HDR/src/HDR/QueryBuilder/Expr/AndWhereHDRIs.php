<?php

namespace HDR\QueryBuilder\Expr;

use Application\QueryBuilder\Expr\AndWhereExpr;
use HDR\Entity\Db\HDR;

class AndWhereHDRIs extends AndWhereExpr
{
    public function __construct(string $alias, HDR $entity)
    {
        parent::__construct($alias);

        $this->where      = "$alias = :hdr";
        $this->parameters = ['hdr' => $entity];
    }

    protected function getJoinSuggestion($rootAlias): string
    {
        return sprintf(
            "Peut-être avez-vous oublié de faire la jointure suivante: '->join(\"%s.hdr\", \"%s\")'.",
            $rootAlias,
            $this->alias
        );
    }
}