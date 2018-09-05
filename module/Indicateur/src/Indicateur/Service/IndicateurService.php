<?php

namespace Indicateur\Service;

use Doctrine\ORM\NonUniqueResultException;
use Indicateur\Model\Indicateur;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class IndicateurService {
    use EntityManagerAwareTrait;

    /**
     * @return Indicateur[]
     */
    public function findAll()
    {
        $qb = $this->getEntityManager()->getRepository(Indicateur::class)->createQueryBuilder("indicateur")
            ->orderBy("indicateur.id")
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param int $id
     * @return Indicateur
     */
    public function find($id)
    {
        $qb = $this->getEntityManager()->getRepository(Indicateur::class)->createQueryBuilder("indicateur")
            ->andWhere("indicateur.id = :id")
            ->setParameter("id", $id)
        ;

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs indicateurs portent le même identifiant [".$id."].");
        }
        if (!$result) throw new RuntimeException("Aucun indicateur ne porte l'identifiant [".$id."].");

        return $result;
    }

    public function fetch($id) {
        $sql = "SELECT * FROM MV_INDICATEUR_".$id;

        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }
}