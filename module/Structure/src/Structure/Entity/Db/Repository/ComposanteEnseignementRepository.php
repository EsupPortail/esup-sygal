<?php

namespace Structure\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use Doctrine\ORM\NonUniqueResultException;
use Structure\Entity\Db\ComposanteEnseignement;
use UnicaenApp\Exception\RuntimeException;

class ComposanteEnseignementRepository extends DefaultEntityRepository
{
    use StructureConcreteRepositoryTrait;

    public function createQueryBuilder($alias, $indexBy = null): DefaultQueryBuilder
    {
        $qb = $this->_createQueryBuilder($alias);

        return $qb;
    }

    /**
     * @return ComposanteEnseignement[]
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder("ce");

        return $this->_findAll($qb);
    }

    /**
     * @return ComposanteEnseignement[]
     */
    public function findSubstituables(): array
    {
        $qb = $this->createQueryBuilder("ce");

        return $this->_findSubstituables($qb);
    }

    /**
     * @param $structureId
     * @return ComposanteEnseignement|null
     */
    public function findByStructureId($structureId): ?ComposanteEnseignement
    {
        $qb = $this->createQueryBuilder("ce");

        return $this->_findByStructureId($qb, $structureId);
    }

    /**
     * @param string|null $term
     * @return ComposanteEnseignement[]
     */
    public function findByText(?string $term) : array
    {
        $qb = $this->createQueryBuilder("ce");

        return $this->_findByText($qb, $term);
    }

    public function find($id, $lockMode = null, $lockVersion = null) : ?ComposanteEnseignement
    {
        /** @var ComposanteEnseignement $composanteEnseignement */
        $qb = $this->createQueryBuilder("u")
            ->addSelect("s")
            ->leftJoin("u.structure", "s")
            ->andWhere("u.id = :id")
            ->setParameter("id", $id);
        try {
            $composanteEnseignement = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("ComposanteEnseignementRepository::find(".$id.") retourne de multiples composantes d'enseignement !");
        }

        return $composanteEnseignement;
    }
}