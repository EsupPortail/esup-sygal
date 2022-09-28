<?php

namespace Structure\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Structure\Entity\Db\UniteRecherche;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

class UniteRechercheRepository extends DefaultEntityRepository
{
    public function createQueryBuilder($alias, $indexBy = null): DefaultQueryBuilder
    {
        $qb = parent::createQueryBuilder($alias, $indexBy);
        $qb
            ->addSelect('structure')
            ->join("$alias.structure", 'structure')
            ->addSelect('structureSubstituante')
            ->leftJoin("structure.structureSubstituante", 'structureSubstituante')
            ->addSelect('uniteRechercheSubstituante')
            ->leftJoin("structureSubstituante.uniteRecherche", 'uniteRechercheSubstituante');

        return $qb;
    }

    /**
     * @param bool $ouverte
     * @return UniteRecherche[]
     */
    public function findAll(bool $ouverte = false): array
    {
        $qb = $this->createQueryBuilder("ur");
        $qb
            ->leftJoin("structure.structuresSubstituees", "sub")
            ->leftJoin("structure.typeStructure", "typ")
            ->addSelect("sub, typ")
            ->orderBy("structure.libelle");

        if ($ouverte) {
            $qb->andWhere('structure.estFermee = false')
                ->andWhere('structureSubstituante IS NULL');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param int|null $id
     * @return UniteRecherche|null
     */
    public function find($id, $lockMode = null, $lockVersion = null) : ?UniteRecherche
    {
        /** @var UniteRecherche $unite */
        $qb = $this->createQueryBuilder("u")
            ->addSelect("s")
            ->leftJoin("u.structure", "s")
            ->andWhere("u.id = :id")
            ->setParameter("id", $id);
        try {
            $unite = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("UniteRechercheRepository::find(".$id.") retourne de multiples unités de recherches !");
        }

        return $unite;
    }

    public function findByStructureId($id)
    {
        /** @var UniteRecherche $unite */
        $qb = $this->createQueryBuilder("u")
            ->andWhere("structure.id = :id")
            ->setParameter("id", $id);
        try {
            $unite = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("UniteRechercheRepository::findByStructureId(".$id.") retourne de multiples unités de recherches !");
        }

        return $unite;
    }

    /**
     * @param string|null $term
     * @return UniteRecherche[]
     */
    public function findByText(?string $term) : array
    {
        $qb = $this->createQueryBuilder("u")
            ->andWhere('lower(structure.libelle) like :term or lower(structure.sigle) like :term')
            ->setParameter('term', '%'.strtolower($term).'%')
            ->andWhere('u.histoDestruction is null')
            ->andWhere('structure.estFermee = :false')
            ->setParameter('false', false)
            ->andWhere('structureSubstituante IS NULL');

        return $qb->getQuery()->getResult();
    }


}