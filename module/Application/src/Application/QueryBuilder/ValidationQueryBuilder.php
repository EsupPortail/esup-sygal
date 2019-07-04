<?php

namespace Application\QueryBuilder;

use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Validation;
use Application\QueryBuilder\Expr\AndWhereTheseIs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;

class ValidationQueryBuilder extends DefaultQueryBuilder
{
    protected $rootAlias = "v";

    public function initWithDefault()
    {
        $this
            ->addSelect("t, tv")
            ->join("$this->rootAlias.these", "t")
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