<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\EcoleDoctorale;
use Application\Entity\Db\Etablissement;
use Application\Entity\Db\UniteRecherche;
use Doctrine\ORM\NonUniqueResultException;
use UnicaenApp\Exception\RuntimeException;

class EcoleDoctoraleRepository extends DefaultEntityRepository
{
    /**
     * @param bool $ouverte
     * @return EcoleDoctorale[]
     */
    public function findAll(bool $ouverte = false)
    {
        $qb = $this->createQueryBuilder("ed");
        $qb
            ->leftJoin("ed.structure", "str", "WITH", "ed.structure = str.id")
            ->leftJoin("str.structuresSubstituees", "sub")
            ->leftJoin("str.typeStructure", "typ")
            ->addSelect("str, sub, typ")
            ->orderBy("str.libelle");

        if ($ouverte) {
            $qb = $qb->andWhere('str.estFermee = false')
                ->leftJoin('str.structureSubstituante', 'substitutionTo')
                ->andWhere('substitutionTo IS NULL')
                ->orderBy('str.sigle')
            ;
        }
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

    /**
     * @param string $code Code national unique de l'ED, ex: '591'
     * @return EcoleDoctorale|null
     */
    public function findOneByCodeStructure($code)
    {
        $qb = $this->getEntityManager()->getRepository(EcoleDoctorale::class)->createQueryBuilder("e")
            ->join("e.structure","structure")
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
            ->addSelect("s")->leftJoin("e.structure", "s")
            ->andWhere('lower(s.libelle) like :term or lower(s.sigle) like :term')
            ->setParameter('term', '%'.strtolower($term).'%')
            ->andWhere('e.histoDestruction is null')
            ->andWhere('s.estFermee = :false')
            ->setParameter('false', false)
            ->leftJoin('s.structureSubstituante', 'substitutionTo')
            ->andWhere('substitutionTo IS NULL')
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }
}