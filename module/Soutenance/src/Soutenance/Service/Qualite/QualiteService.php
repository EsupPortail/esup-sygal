<?php

namespace Soutenance\Service\Qualite;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Soutenance\Entity\Qualite;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class QualiteService {
    use EntityManagerAwareTrait;

    public function getQualiteById($id) {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder("qualite")
            ->andWhere("qualite.id = :id")
            ->setParameter("id", $id);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs qualité partagent le même identifiant !");
        }
        return $result;
    }

    public function getQualiteByLibelle($libelle) {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder("qualite")
            ->andWhere("qualite.libelle = :libelle")
            ->setParameter("libelle", $libelle);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs qualité partagent le même identifiant !");
        }
        return $result;
    }


    public function findAllQualites()
    {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder('qualite')
            ->orderBy('qualite.rang, qualite.libelle');
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function createQualite($qualite)
    {
        $this->getEntityManager()->persist($qualite);
        try {
            $this->getEntityManager()->flush($qualite);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'enregistrement en BD d'une nouvelle qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     * @return Qualite
     */
    public function updateQualite($qualite)
    {
        try {
            $this->getEntityManager()->flush($qualite);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de la mise à jour en BD d'une qualité.");
        }

        return $qualite;
    }

    /**
     * @param Qualite $qualite
     */
    public function removeQualite($qualite)
    {
        $this->getEntityManager()->remove($qualite);
        try {
            $this->getEntityManager()->flush($qualite);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème s'est produit lors de l'effacement en BD d'une nouvelle qualité.");
        }
    }
}