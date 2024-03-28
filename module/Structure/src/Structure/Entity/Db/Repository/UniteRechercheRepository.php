<?php

namespace Structure\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use Doctrine\ORM\NonUniqueResultException;
use Structure\Entity\Db\UniteRecherche;
use UnicaenApp\Exception\RuntimeException;

class UniteRechercheRepository extends DefaultEntityRepository
{
    use StructureConcreteRepositoryTrait;

    public function createQueryBuilder($alias, $indexBy = null): DefaultQueryBuilder
    {
        $qb = $this->_createQueryBuilder($alias);

        $qb
            ->orderBy('structure.sigle')
            ->addOrderBy('structure.libelle');

        return $qb;
    }

    /**
     * @return UniteRecherche[]
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder("ur");
        $qb->orderBy("coalesce(structure.sigle, 'aaaaa'), structure.libelle");

        return $this->_findAll($qb);
    }

    public function findByStructureId($structureId, bool $nonHistorise = true): ?UniteRecherche
    {
        $qb = $this->createQueryBuilder("ur");
        if ($nonHistorise) {
            $qb->andWhereNotHistorise('ur');
        }

        return $this->_findByStructureId($qb, $structureId);
    }

    /**
     * Recherche textuelle d'UR.
     *
     * @return array[] Entités hydratées au format tableau.
     */
    public function findByText(?string $term) : array
    {
        if (strlen($term) < 2) return [];

        $qb = $this->findByTextQb($term);

        return $qb->getQuery()->getArrayResult();
    }

    public function findByTextQb(?string $term): DefaultQueryBuilder
    {
        $qb = $this->createQueryBuilder("ur");

        return $this->_findByTextQb($qb, $term);
    }

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
}