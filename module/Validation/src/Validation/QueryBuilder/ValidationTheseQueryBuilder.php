<?php

namespace Validation\QueryBuilder;

use These\Entity\Db\These;
use These\QueryBuilder\AndWhereTheseIs;

class ValidationTheseQueryBuilder extends AbstractValidationEntityQueryBuilder
{
    public function initWithDefault(): static
    {
        return parent::initWithDefault()
            ->addSelect("t")
            ->join("$this->rootAlias.these", "t");
    }

    public function andWhereTheseIs(These $these): static
    {
        return $this->applyExpr(new AndWhereTheseIs("t", $these)); // alias si pas de jointure: "$this->rootAlias.these"
    }
}