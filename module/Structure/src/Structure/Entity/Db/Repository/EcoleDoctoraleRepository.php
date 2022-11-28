<?php

namespace Structure\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use Structure\Entity\Db\EcoleDoctorale;

class EcoleDoctoraleRepository extends DefaultEntityRepository
{
    use StructureConcreteRepositoryTrait;

    public function createQueryBuilder($alias, $indexBy = null): DefaultQueryBuilder
    {
        $qb = $this->_createQueryBuilder($alias);
        $qb
            ->addSelect('ecoleDoctoraleSubstituante')
            ->leftJoin("structureSubstituante.ecoleDoctorale", 'ecoleDoctoraleSubstituante');

        return $qb;
    }

    /**
     * @return EcoleDoctorale[]
     */
    public function findAll(): array
    {
        $qb = $this->createQueryBuilder("ed");

        return $this->_findAll($qb);
    }

    /**
     * @return EcoleDoctorale[]
     */
    public function findSubstituables(): array
    {
        $qb = $this->createQueryBuilder("ed");

        return $this->_findSubstituables($qb);
    }

    /**
     * @param $structureId
     * @return \Structure\Entity\Db\EcoleDoctorale|null
     */
    public function findByStructureId($structureId): ?EcoleDoctorale
    {
        $qb = $this->createQueryBuilder("ed");

        return $this->_findByStructureId($qb, $structureId);
    }

    /**
     * @param string|null $term
     * @return EcoleDoctorale[]
     */
    public function findByText(?string $term) : array
    {
        $qb = $this->createQueryBuilder("ed");

        return $this->_findByText($qb, $term);
    }
}