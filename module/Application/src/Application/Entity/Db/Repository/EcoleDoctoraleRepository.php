<?php

namespace Application\Entity\Db\Repository;


use Application\Entity\Db\EcoleDoctorale;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\RuntimeException;
use UnicaenImport\Entity\Db\Source;

class EcoleDoctoraleRepository extends DefaultEntityRepository
{
    /**
     * @param Source|null $source
     * @return EcoleDoctorale[]
     */
    public function findAll(Source $source = null)
    {
        $qb = $this->createQueryBuilder("ed");
        $qb
            ->leftJoin("ed.structure", "str", "WITH", "ed.structure = str.id")
            ->leftJoin("str.structuresSubstituees", "sub")
            ->leftJoin("str.typeStructure", "typ")
            ->addSelect("str, sub, typ")
            ->orderBy("str.libelle");

        if ($source !== null) {
            $qb
                ->join('ed.source', 'src', Join::WITH, 'src = :source')
                ->setParameter('source', $source);
        }

        $ecoles = $qb->getQuery()->getResult();

        return $ecoles;
    }

    /**
     * @param int $id
     * @return null|EcoleDoctorale
     */
    public function find($id)
    {
        /** @var EcoleDoctorale $ecole */
        $ecole = $this->findOneBy(["id" => $id]);

        return $ecole;
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