<?php

namespace Application\QueryBuilder;

use Application\Entity\Db\Fichier;
use Application\Entity\Db\These;
use Application\QueryBuilder\Expr\AndWhereTheseIs;
use Doctrine\ORM\EntityManagerInterface;

class FichierQueryBuilder extends DefaultQueryBuilder
{
    protected $rootAlias = "f";

    public function __construct(EntityManagerInterface $em, $rootAlias = null)
    {
        parent::__construct($em, $rootAlias);

        $this->initWithDefault();
    }

    public function initWithDefault()
    {
        $this
            ->from(Fichier::class, $this->rootAlias)
            ->select("$this->rootAlias, t")
            ->join("$this->rootAlias.these", "t")
//            ->orderBy("p.nomUsuel, i.type, i.ordre")
        ;

        return $this;
    }

    public function andWhereTheseIs(These $these)
    {
        return $this->applyExpr(new AndWhereTheseIs("t", $these)); // alias si pas de jointure: "$this->rootAlias.these"
    }
}