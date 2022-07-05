<?php

namespace Formation\Service\SessionStructureComplementaire;

use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Repository\SessionStructureComplementaireRepository;
use Formation\Entity\Db\SessionStructureComplementaire;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class SessionStructureComplementaireService
{
    use EntityManagerAwareTrait;

    /**
     * @return SessionStructureComplementaireRepository
     */
    public function getRepository(): SessionStructureComplementaireRepository
    {
        /** @var SessionStructureComplementaireRepository $repo */
        $repo = $this->entityManager->getRepository(SessionStructureComplementaire::class);
        return $repo;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param SessionStructureComplementaire $sessionStructureComplementaire
     * @return SessionStructureComplementaire
     */
    public function create(SessionStructureComplementaire $sessionStructureComplementaire): SessionStructureComplementaire
    {
        try {
            $this->getEntityManager()->persist($sessionStructureComplementaire);
            $this->getEntityManager()->flush($sessionStructureComplementaire);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survenu en base pour une entité [Session]", 0, $e);
        }
        return $sessionStructureComplementaire;
    }

    /**
     * @param SessionStructureComplementaire $sessionStructureComplementaire
     * @return SessionStructureComplementaire
     */
    public function update(SessionStructureComplementaire $sessionStructureComplementaire): SessionStructureComplementaire
    {
        try {
            $this->getEntityManager()->flush($sessionStructureComplementaire);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Session]", 0, $e);
        }
        return $sessionStructureComplementaire;
    }

    /**
     * @param SessionStructureComplementaire $sessionStructureComplementaire
     * @return SessionStructureComplementaire
     */
    public function historise(SessionStructureComplementaire $sessionStructureComplementaire): SessionStructureComplementaire
    {
        try {
            $sessionStructureComplementaire->historiser();
            $this->getEntityManager()->flush($sessionStructureComplementaire);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [SessionStructureComplementaire]", 0, $e);
        }
        return $sessionStructureComplementaire;
    }

    /**
     * @param SessionStructureComplementaire $sessionStructureComplementaire
     * @return SessionStructureComplementaire
     */
    public function restore(SessionStructureComplementaire $sessionStructureComplementaire): SessionStructureComplementaire
    {
        try {
            $sessionStructureComplementaire->dehistoriser();
            $this->getEntityManager()->flush($sessionStructureComplementaire);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [SessionStructureComplementaire]", 0, $e);
        }
        return $sessionStructureComplementaire;
    }

    /**
     * @param SessionStructureComplementaire $sessionStructureComplementaire
     * @return SessionStructureComplementaire
     */
    public function delete(SessionStructureComplementaire $sessionStructureComplementaire): SessionStructureComplementaire
    {
        try {
            $this->getEntityManager()->remove($sessionStructureComplementaire);
            $this->getEntityManager()->flush($sessionStructureComplementaire);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [SessionStructureComplementaire]", 0, $e);
        }
        return $sessionStructureComplementaire;
    }
}