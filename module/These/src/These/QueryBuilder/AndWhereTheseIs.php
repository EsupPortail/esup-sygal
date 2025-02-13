<?php

namespace These\QueryBuilder;

use Application\QueryBuilder\Expr\AndWhereExpr;
use These\Entity\Db\These;

class AndWhereTheseIs extends AndWhereExpr
{
    public function __construct(string $alias, These $entity)
    {
        parent::__construct($alias);

        $this->where      = "$alias = :these";
        $this->parameters = ['these' => $entity];
    }

    protected function getJoinSuggestion($rootAlias): string
    {
        return sprintf(
            "Peut-être avez-vous oublié de faire la jointure suivante: '->join(\"%s.these\", \"%s\")'.",
            $rootAlias,
            $this->alias
        );
    }
}