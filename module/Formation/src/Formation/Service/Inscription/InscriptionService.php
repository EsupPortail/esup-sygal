<?php

namespace Formation\Service\Inscription;

use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Repository\InscriptionRepository;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class InscriptionService {
    use EntityManagerAwareTrait;

    /**
     * @return InscriptionRepository
     */
    public function getRepository() : InscriptionRepository
    {
        /** @var InscriptionRepository $repo */
        $repo = $this->entityManager->getRepository(Inscription::class);
        return $repo;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Inscription $seance
     * @return Inscription
     */
    public function create(Inscription $seance) : Inscription
    {
        try {
            $this->getEntityManager()->persist($seance);
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Inscription]",0, $e);
        }
        return $seance;
    }

    /**
     * @param Inscription $seance
     * @return Inscription
     */
    public function update(Inscription $seance) : Inscription
    {
        try {
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Inscription]",0, $e);
        }
        return $seance;
    }

    /**
     * @param Inscription $seance
     * @return Inscription
     */
    public function historise(Inscription $seance) : Inscription
    {
        try {
            $seance->historiser();
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Inscription]",0, $e);
        }
        return $seance;
    }

    /**
     * @param Inscription $seance
     * @return Inscription
     */
    public function restore(Inscription $seance) : Inscription
    {
        try {
            $seance->dehistoriser();
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Inscription]",0, $e);
        }
        return $seance;
    }

    /**
     * @param Inscription $seance
     * @return Inscription
     */
    public function delete(Inscription $seance) : Inscription
    {
        try {
            $this->getEntityManager()->remove($seance);
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Inscription]",0, $e);
        }
        return $seance;
    }

    /** FACADE ********************************************************************************************************/
}