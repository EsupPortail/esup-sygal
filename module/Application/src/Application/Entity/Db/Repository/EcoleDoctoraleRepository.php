<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\EcoleDoctorale;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

class EcoleDoctoraleRepository extends DefaultEntityRepository
{
    /**
     * @return EcoleDoctorale[]
     */
    public function findAll()
    {
        $qb = $this->createQueryBuilder("ed");
        $qb
            ->leftJoin("ed.structure", "str", "WITH", "ed.structure = str.id")
            ->leftJoin("str.structuresSubstituees", "sub")
            ->leftJoin("str.typeStructure", "typ")
            ->addSelect("str, sub, typ")
            ->orderBy("str.libelle");

        $ecoles = $qb->getQuery()->getResult();

        return $ecoles;
    }

    public function findByStructureId($id)
    {
        /** @var EcoleDoctorale $ecole */
        $qb = $this->createQueryBuilder("ed")
            ->addSelect("s")
            ->leftJoin("ed.structure", "s")
            ->andWhere("s.id = :id")
            ->setParameter("id", $id);
        try {
            $ecole = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("EcoleDoctoraleRepository::findByStructureId(".$id.") retourne de multiples écoles doctorales !");
        }

        return $ecole;
    }
}