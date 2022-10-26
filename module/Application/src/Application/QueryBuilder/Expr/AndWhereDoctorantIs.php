<?php

namespace Application\QueryBuilder\Expr;

use Doctorant\Entity\Db\Doctorant;

class AndWhereDoctorantIs extends AndWhereExpr
{
    public function __construct(Doctorant $entity, $alias)
    {
        parent::__construct($alias);

        $this->where      = "$alias = :doctorant";
        $this->parameters = ['doctorant' => $entity];
    }

    protected function getJoinSuggestion($rootAlias): string
    {
        return sprintf(
            "Peut-être avez-vous oublié de faire la jointure suivante: '->join(\"%s.doctorant\", \"%s\")'.",
            $rootAlias,
            $this->alias
        );
    }
}