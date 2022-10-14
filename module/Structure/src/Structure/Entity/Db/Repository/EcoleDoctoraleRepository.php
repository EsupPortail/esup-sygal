<?php

namespace Structure\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Application\QueryBuilder\DefaultQueryBuilder;
use Doctrine\ORM\NonUniqueResultException;
use Structure\Entity\Db\EcoleDoctorale;
use UnicaenApp\Exception\RuntimeException;

class EcoleDoctoraleRepository extends DefaultEntityRepository
{
    public function createQueryBuilder($alias, $indexBy = null): DefaultQueryBuilder
    {
        $qb = parent::createQueryBuilder($alias, $indexBy);
        $qb
            ->addSelect('structure')
            ->join("$alias.structure", 'structure')
            ->addSelect('structureSubstituante')
            ->leftJoin("structure.structureSubstituante", 'structureSubstituante')
            ->addSelect('ecoleDoctoraleSubstituante')
            ->leftJoin("structureSubstituante.ecoleDoctorale", 'ecoleDoctoraleSubstituante');

        return $qb;
    }

    /**
     * @param bool $ouverte
     * @return EcoleDoctorale[]
     */
    public function findAll(bool $ouverte = false): array
    {
        $qb = $this->createQueryBuilder("ed");
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

    public function findByStructureId($id)
    {
        /** @var EcoleDoctorale $ecole */
        $qb = $this->createQueryBuilder("ed")
            ->andWhere("structure.id = :id")
            ->setParameter("id", $id);
        try {
            $ecole = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("EcoleDoctoraleRepository::findByStructureId(".$id.") retourne de multiples écoles doctorales !");
        }

        return $ecole;
    }

    /**
     * @param string $code Code national unique de l'ED, ex: '591'
     * @return EcoleDoctorale|null
     */
    public function findOneByCodeStructure(string $code): ?EcoleDoctorale
    {
        $qb = $this->createQueryBuilder("e")
            ->andWhere("structure.code = :code")
            ->setParameter("code", $code);

        /** @var EcoleDoctorale $entity */
        try {
            $entity = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie: plusieures ED ont le même code structure.");
        }

        return $entity;
    }

    /**
     * @param string|null $term
     * @return EcoleDoctorale[]
     */
    public function findByText(?string $term) : array
    {
        $qb = $this->createQueryBuilder("e")
            ->andWhere('lower(structure.libelle) like :term or lower(structure.sigle) like :term')
            ->setParameter('term', '%'.strtolower($term).'%')
            ->andWhere('e.histoDestruction is null')
            ->andWhere('structure.estFermee = :false')
            ->setParameter('false', false)
            ->andWhere('structureSubstituante IS NULL');

        return $qb->getQuery()->getResult();
    }
}