<?php

namespace Validation\QueryBuilder;

use HDR\Entity\Db\HDR;
use HDR\QueryBuilder\Expr\AndWhereHDRIs;

class ValidationHDRQueryBuilder extends AbstractValidationEntityQueryBuilder
{
    public function initWithDefault(): static
    {
        return parent::initWithDefault()
            ->addSelect("t")
            ->join("$this->rootAlias.hdr", "t");
    }

    public function andWhereHDRIs(HDR $hdr): static
    {
        return $this->applyExpr(new AndWhereHDRIs("t", $hdr)); // alias si pas de jointure: "$this->rootAlias.hdr"
    }
}