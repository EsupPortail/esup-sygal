<?php

namespace Application\QueryBuilder;

use These\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\QueryBuilder\Expr\AndWhereTheseIs;

class ValidationQueryBuilder extends DefaultQueryBuilder
{
    protected $rootAlias = "v";

    public function initWithDefault(): self
    {
        $this
            ->addSelect("t, tv, i")
            ->join("$this->rootAlias.these", "t")
            ->leftJoin("$this->rootAlias.individu", "i")
            ->join("$this->rootAlias.typeValidation", 'tv')
        ;

        return $this;
    }

    /**
     * @param These $these
     * @return static
     */
    public function andWhereTheseIs(These $these)
    {
        return $this->applyExpr(new AndWhereTheseIs("t", $these)); // alias si pas de jointure: "$this->rootAlias.these"
    }

    /**
     * @param $typeValidation
     * @return static
     */
    public function andWhereTypeIs($typeValidation)
    {
        if ($typeValidation instanceof TypeValidation) {
            $typeValidation = $typeValidation->getCode();
        }

        $this
            ->andWhere('tv.code = :tvcode')
            ->setParameter('tvcode', $typeValidation);

        return $this;
    }
}