<?php

namespace Validation\QueryBuilder;

use Formation\Entity\Db\FormationExterne;
use Formation\QueryBuilder\Expr\AndWhereFormationExterneIs;

class ValidationFormationExterneQueryBuilder extends AbstractValidationEntityQueryBuilder
{
    public function initWithDefault(): static
    {
        return parent::initWithDefault()
            ->addSelect("fe")
            ->join("$this->rootAlias.formationExterne", "fe");
    }

    public function andWhereFormationExterneIs(FormationExterne $formationExterne): static
    {
        return $this->applyExpr(new AndWhereFormationExterneIs("fe", $formationExterne)); // alias si pas de jointure: "$this->rootAlias.hdr"
    }
}