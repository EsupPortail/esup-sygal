<?php

namespace Formation\QueryBuilder\Expr;

use Application\QueryBuilder\Expr\AndWhereExpr;
use Formation\Entity\Db\FormationExterne;

class AndWhereFormationExterneIs extends AndWhereExpr
{
    public function __construct(string $alias, FormationExterne $entity)
    {
        parent::__construct($alias);

        $this->where      = "$alias = :formationExterne";
        $this->parameters = ['formationExterne' => $entity];
    }

    protected function getJoinSuggestion($rootAlias): string
    {
        return sprintf(
            "Peut-être avez-vous oublié de faire la jointure suivante: '->join(\"%s.formationExterne\", \"%s\")'.",
            $rootAlias,
            $this->alias
        );
    }
}