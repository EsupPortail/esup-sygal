<?php

namespace Application\QueryBuilder;

use Application\QueryBuilder\Expr\AndWhereExpr;
use Application\QueryBuilder\Expr\AndWhereHistorise;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Structure\Entity\Db\StructureInterface;

/**
 * Class QueryBuilder
 *
 * @package Application\QueryBuilder
 */
class DefaultQueryBuilder extends QueryBuilder
{
    protected $rootAlias;

    /**
     * AbstractQueryBuilder constructor.
     *
     * @param EntityManagerInterface $em
     * @param null                   $rootAlias
     */
    public function __construct(EntityManagerInterface $em, $rootAlias = null)
    {
        parent::__construct($em);

        $this->rootAlias = $rootAlias ?: $this->rootAlias;
    }

    /**
     * @return self
     */
    public function initWithDefault(): self
    {
        return $this;
    }

    /**
     * @param AndWhereExpr $expr
     * @return self
     */
    protected function applyExpr(AndWhereExpr $expr): self
    {
        $expr->applyToQueryBuilder($this);

        return $this;
    }

    /**
     * @param string|null $alias
     * @return self
     */
    public function andWhereNotHistorise(string $alias = null): self
    {
        return $this->applyExpr(new AndWhereHistorise($alias ?: $this->rootAlias, false));
    }


    /////////////////////////////// Jointures et filtres concernant la Structure liée \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

    /**
     * Ajoute à la requête le filtre pour ne retenir que les enregistrements dont la
     * {@see \Structure\Entity\Db\Structure} jointe est la structure spécifiée.
     *
     * @param \Structure\Entity\Db\StructureInterface $structure La structure (abstraite) concernée
     * @param string $structureAlias Renseignez cet argument pour spécifier l'alias de la jointure vers la
     * {@see \Structure\Entity\Db\Structure} liée (qui doit donc exister au préalable dans la requête).
     */
    public function andWhereStructureIs(StructureInterface $structure, string $structureAlias = 's'): self
    {
        $parameterName = uniqid($structureAlias . '_param_'); // évite les collisions de noms

        $this
            ->andWhere("$structureAlias = :$parameterName")
            ->setParameter($parameterName, $structure);

        return $this;
    }
}