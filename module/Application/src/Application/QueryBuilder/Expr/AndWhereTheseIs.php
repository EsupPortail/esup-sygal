<?php

namespace Application\QueryBuilder\Expr;

use Application\Entity\Db\These;

class AndWhereTheseIs extends AndWhereExpr
{
    /**
     * AndWhereTheseIs constructor.
     *
     * @param string $alias
     * @param These  $entity
     */
    public function __construct($alias, These $entity)
    {
        parent::__construct($alias);

        $this->where      = "$alias = :these";
        $this->parameters = ['these' => $entity];
    }

    protected function getJoinSuggestion($rootAlias)
    {
        return sprintf(
            "Peut-être avez-vous oublié de faire la jointure suivante: '->join(\"%s.these\", \"%s\")'.",
            $rootAlias,
            $this->alias
        );
    }
}