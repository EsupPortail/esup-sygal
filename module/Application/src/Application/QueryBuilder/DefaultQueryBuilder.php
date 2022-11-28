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
     * Ajoute à la requête la jointure ouverte vers la {@see \Structure\Entity\Db\Structure} substituante éventuelle.
     *
     * **NB : L'appelant de cette méthode
     * doit avoir fait au préalable la jointure (ouverte ou fermée) vers la {@see \Structure\Entity\Db\Structure}
     * et spécifier en argument l'alias utilisé pour cette jointure.**
     *
     * @param string $structureAlias Renseignez cet argument pour spécifier l'alias de la jointure vers la
     * {@see \Structure\Entity\Db\Structure} liée (qui doit donc exister au préalable dans la requête).
     * @param string $structureSubstituanteAlias Renseignez cet argument pour spécifier l'alias à donner à la jointure vers la
     * {@see \Structure\Entity\Db\Structure} substituante.
     *
     * @return self
     */
    public function leftJoinStructureSubstituante(
        string $structureAlias = 's',
        string $structureSubstituanteAlias = 'structureSubstituante'): self
    {
        $this
            ->leftJoin($structureAlias . '.structureSubstituante', $structureSubstituanteAlias)
            ->addSelect($structureSubstituanteAlias);

        return $this;
    }

    /**
     * Ajoute à la requête le filtre pour ne retenir que les enregistrements dont la
     * {@see \Structure\Entity\Db\Structure} jointe est :
     * - soit la structure spécifiée,
     * - soit la structure qui la substitue.
     *
     * **NB : Sauf si une jointure 'structureSubstituante' existe déjà dans la requête, l'appelant de cette méthode
     * doit avoir fait au préalable la jointure (ouverte ou fermée) vers la {@see \Structure\Entity\Db\Structure}
     * et spécifier en argument l'alias utilisé pour cette jointure.**
     *
     * @param \Structure\Entity\Db\StructureInterface $structure La structure (abstraite) concernée
     * @param string $structureAlias Dans le cas où aucune jointure 'structureSubstituante' n'existe déjà dans la
     * requête, renseignez cet argument pour spécifier l'alias de la jointure vers la
     * {@see \Structure\Entity\Db\Structure} liée (qui doit donc exister au préalable dans la requête).
     */
    public function andWhereStructureOuSubstituanteIs(StructureInterface $structure, string $structureAlias = 's'): self
    {
        $parameterName = uniqid($structureAlias . '_param_'); // évite les collisions de noms

        if (in_array('structureSubstituante', $this->getAllAliases())) {
            $structureSubstituanteAlias = 'structureSubstituante';
        } else {
            $structureSubstituanteAlias = uniqid($structureAlias . '_structureSubstituante_'); // évite les collisions d'alias
            $this->leftJoinStructureSubstituante($structureAlias, $structureSubstituanteAlias);
        }
        $this
            ->andWhere("$structureAlias = :$parameterName OR $structureSubstituanteAlias = :$parameterName")
            ->setParameter($parameterName, $structure);

        return $this;
    }

    /**
     * Ajoute à la requête la jointure ouverte vers les {@see \Structure\Entity\Db\Structure}s substituées éventuelles.
     *
     * **NB : L'appelant de cette méthode
     * doit avoir fait au préalable la jointure (ouverte ou fermée) vers la {@see \Structure\Entity\Db\Structure}
     * et spécifier en argument l'alias utilisé pour cette jointure.**
     *
     * @param string $structureAlias Renseignez cet argument pour spécifier l'alias de la jointure vers la
     * {@see \Structure\Entity\Db\Structure} liée (qui doit donc exister au préalable dans la requête).
     * @param string $structuresSubstitueesAlias Renseignez cet argument pour spécifier l'alias à donner à la jointure vers les
     * {@see \Structure\Entity\Db\Structure}s substituées.
     *
     * @return self
     */
    public function leftJoinStructuresSubstituees(
        string $structureAlias = 's',
        string $structuresSubstitueesAlias = 'structuresSubstituees'): self
    {
        $this
            ->leftJoin($structureAlias . '.structuresSubstituees', $structuresSubstitueesAlias)
            ->addSelect($structuresSubstitueesAlias);

        return $this;
    }

    /**
     * Ajoute à la requête le filtre pour ne retenir que les enregistrements dont la
     * {@see \Structure\Entity\Db\Structure} jointe n'est pas substituée.
     *
     * **NB : Sauf si une jointure 'structureSubstituante' existe déjà dans la requête, l'appelant de cette méthode
     * doit avoir fait au préalable la jointure (ouverte ou fermée) vers la {@see \Structure\Entity\Db\Structure}
     * et spécifier en argument l'alias utilisé pour cette jointure.**
     *
     * @param string $structureAlias Dans le cas où aucune jointure 'structureSubstituante' n'existe déjà dans la
     * requête, renseignez cet argument pour spécifier l'alias de la jointure vers la
     * {@see \Structure\Entity\Db\Structure} liée (qui doit donc exister au préalable dans la requête).
     */
    public function andWhereStructureEstNonSubstituee(string $structureAlias = 's'): self
    {
        if (in_array('structureSubstituante', $this->getAllAliases())) {
            $structureSubstituanteAlias = 'structureSubstituante';
        } else {
            $structureSubstituanteAlias = uniqid($structureAlias . '_structureSubstituante_'); // évite les collisions d'alias
            $this->leftJoinStructureSubstituante($structureAlias, $structureSubstituanteAlias);
        }
        $this->andWhere($structureSubstituanteAlias . ' is null');

        return $this;
    }

    /**
     * Ajoute à la requête le filtre pour ne retenir que les enregistrements dont la
     * {@see \Structure\Entity\Db\Structure} jointe ne substitue aucune structure.
     *
     * **NB : Sauf si une jointure 'structuresSubstituees' existe déjà dans la requête, l'appelant de cette méthode
     * doit avoir fait au préalable la jointure ouverte vers la {@see \Structure\Entity\Db\Structure}
     * et spécifier en argument l'alias utilisé pour cette jointure.**
     *
     * @param string $structureAlias Dans le cas où aucune jointure 'structuresSubstituees' n'existe déjà dans la
     * requête, renseignez cet argument pour spécifier l'alias de la jointure vers la
     * {@see \Structure\Entity\Db\Structure} liée (qui doit donc exister au préalable dans la requête).
     */
    public function andWhereStructureEstNonSubstituante(string $structureAlias = 's'): self
    {
        if (in_array('structuresSubstituees', $this->getAllAliases())) {
            $structuresSubstitueesAlias = 'structuresSubstituees';
        } else {
            $structuresSubstitueesAlias = uniqid($structureAlias . '_structuresSubstituees_'); // évite les collisions d'alias
            $this->leftJoinStructuresSubstituees($structureAlias, $structuresSubstitueesAlias);
        }
        $this->andWhere($structuresSubstitueesAlias . ' is null');

        return $this;
    }
}