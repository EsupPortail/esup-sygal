<?php

namespace Soutenance\Service\Proposition;

//TODO faire le repo aussi
use Application\Entity\Db\These;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Soutenance\Entity\Membre;
use Soutenance\Entity\Proposition;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class PropositionService {
    use EntityManagerAwareTrait;

    /**
     * @param int $id
     * @return Proposition
     */
    public function find($id) {
        $qb = $this->getEntityManager()->getRepository(Proposition::class)->createQueryBuilder("proposition")
            ->andWhere("proposition.id = :id")
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
     * @param These $these
     * @return Proposition
     */
    public function findByThese($these) {
        $qb = $this->getEntityManager()->getRepository(Proposition::class)->createQueryBuilder("proposition")
            ->andWhere("proposition.these = :these")
            ->setParameter("these", $these)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiples propositions associé à la thèse [".$these->getId()."] ont été trouvées !");
        }
        return $result;
    }


    /**
     * @return Proposition[]
     */
    public function findAll() {
        $qb = $this->getEntityManager()->getRepository(Proposition::class)->createQueryBuilder("proposition")
            ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Proposition $proposition
     */
    public function update($proposition)
    {
        try {
            $this->getEntityManager()->flush($proposition);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Une erreur s'est produite lors de la mise à jour de la proposition de soutenance !");
        }
    }

    public function findMembre($idMembre)
    {
        $qb = $this->getEntityManager()->getRepository(Membre::class)->createQueryBuilder("membre")
            ->andWhere("membre.id = :id")
            ->setParameter("id", $idMembre)
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("De multiple membres sont associés à l'identifiant [".$idMembre."] !");
        }
        return $result;
    }
}