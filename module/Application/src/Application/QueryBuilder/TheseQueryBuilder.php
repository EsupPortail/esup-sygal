<?php

namespace Application\QueryBuilder;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\These;
use Application\QueryBuilder\Expr\AndWhereDoctorantIs;
use Doctrine\ORM\Query\Expr\Join;

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

    /**
     * @param string $alias
     * @return $this
     */
    public function joinDoctorant($alias = 'd')
    {
        $this->join("$this->rootAlias.doctorant", $alias);

        return $this;
    }

    /**
     * @param string $alias
     * @return $this
     */
    public function leftJoinEcoleDoctorale($alias = 'ed')
    {
        $this
            ->leftJoin("$this->rootAlias.ecoleDoctorale", $alias)
            ->leftJoin("$alias.structure", uniqid("struct"));

        return $this;
    }

    /**
     * @param string $alias
     * @return $this
     */
    public function leftJoinUniteRecherche($alias = 'ur')
    {
        $this
            ->leftJoin("$this->rootAlias.uniteRecherche", $alias)
            ->leftJoin("$alias.structure", uniqid("struct"));

        return $this;
    }

    /**
     * @param string      $alias
     * @param string|null $codeRole
     * @return $this
     */
    public function leftJoinActeur($alias = 'a', $codeRole = null)
    {
        if ($codeRole !== null) {
            $this
                ->leftJoin("$this->rootAlias.acteurs", $alias)
                ->leftJoin("$alias.role", uniqid("role"), Join::WITH, "r.code = :codeRole")
                ->setParameter("codeRole", $codeRole);
        } else {
            $this
                ->leftJoin("$this->rootAlias.acteurs", $alias);
        }

        return $this;
    }
}