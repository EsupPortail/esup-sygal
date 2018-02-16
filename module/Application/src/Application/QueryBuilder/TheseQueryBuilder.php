<?php

namespace Application\QueryBuilder;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\These;
use Application\QueryBuilder\Expr\AndWhereDoctorantIs;

/**
 * Class TheseQueryBuilder
 */
class TheseQueryBuilder extends DefaultQueryBuilder
{
    protected $rootAlias = "t";

    /**
     * @return $this
     */
    public function initWithDefault()
    {
        $this
            ->addSelect("th")
            ->join("$this->rootAlias.doctorant", "th")
            ->andWhere('1 = pasHistorise(th)')
        ;

        return $this;
    }

    /**
     * @param Doctorant $doctorant
     * @param string    $alias
     * @return $this
     */
    public function andWhereDoctorantIs(Doctorant $doctorant, $alias = 'th')
    {
        return $this->applyExpr(new AndWhereDoctorantIs($doctorant, $alias)); // alias si pas de jointure: "$this->rootAlias.doctorant"
    }

    /**
     * @param string $etat Ex: These::ETAT_EN_COURS
     * @return $this
     * @see These
     */
    public function andWhereEtatIs($etat)
    {
        $this
            ->andWhere("$this->rootAlias.etatThese = :" . ($name = uniqid('etat')))
            ->setParameter($name, $etat);

        return $this;
    }
}