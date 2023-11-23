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

        return $qb;
    }

    /**
     * @return UniteRecherche[]
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder("ur");

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
     * @param string|null $term
     * @return UniteRecherche[]
     */
    public function findByText(?string $term) : array
    {
        $qb = $this->createQueryBuilder("ur");

        return $this->_findByText($qb, $term);
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