<?php

namespace Soutenance\Service\Membre;

//TODO faire le repo aussi
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use Soutenance\Entity\Qualite;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class MembreService {
    use EntityManagerAwareTrait;

    /**
     * @param int $id
     * @return Proposition
     */
    public function find($id) {
        $qb = $this->getEntityManager()->getRepository(Membre::class)->createQueryBuilder("membre")
            ->andWhere("membre.id = :id")
            ->setParameter("id", $id)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiples propositions identifiées [".$id."] ont été trouvées !");
        }
        return $result;
    }

    /**
     * @param Membre $membre
     */
    public function create($membre)
    {
        $this->getEntityManager()->persist($membre);
        try {
            $this->getEntityManager()->flush($membre);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de l'enregistrement d'un membre de jury !");
        }
    }

    /**
     * @param Membre $membre
     */
    public function update($membre) {
        try {
            $this->getEntityManager()->flush($membre);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour d'un membre de jury !");
        }
    }

    /**
     * @param Membre $membre
     */
    public function delete($membre) {
        $this->getEntityManager()->remove($membre);
        try {
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de l'effacement d'un membre de jury !");
        }
    }

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

    public function findAllQualites()
    {
        $qb = $this->getEntityManager()->getRepository(Qualite::class)->createQueryBuilder('qualite')
            ->orderBy('qualite.rang');
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
