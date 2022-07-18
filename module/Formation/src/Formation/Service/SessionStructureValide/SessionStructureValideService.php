<?php

namespace Formation\Service\SessionStructureValide;

use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Repository\SessionStructureValideRepository;
use Formation\Entity\Db\SessionStructureValide;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class SessionStructureValideService
{
    use EntityManagerAwareTrait;

    /**
     * @return SessionStructureValideRepository
     */
    public function getRepository(): SessionStructureValideRepository
    {
        /** @var SessionStructureValideRepository $repo */
        $repo = $this->entityManager->getRepository(SessionStructureValide::class);
        return $repo;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param SessionStructureValide $sessionStructureComplementaire
     * @return SessionStructureValide
     */
    public function create(SessionStructureValide $sessionStructureComplementaire): SessionStructureValide
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
     * @param SessionStructureValide $sessionStructureComplementaire
     * @return SessionStructureValide
     */
    public function update(SessionStructureValide $sessionStructureComplementaire): SessionStructureValide
    {
        try {
            $this->getEntityManager()->flush($sessionStructureComplementaire);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Session]", 0, $e);
        }
        return $sessionStructureComplementaire;
    }

    /**
     * @param SessionStructureValide $sessionStructureComplementaire
     * @return SessionStructureValide
     */
    public function historise(SessionStructureValide $sessionStructureComplementaire): SessionStructureValide
    {
        try {
            $sessionStructureComplementaire->historiser();
            $this->getEntityManager()->flush($sessionStructureComplementaire);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [SessionStructureValide]", 0, $e);
        }
        return $sessionStructureComplementaire;
    }

    /**
     * @param SessionStructureValide $sessionStructureComplementaire
     * @return SessionStructureValide
     */
    public function restore(SessionStructureValide $sessionStructureComplementaire): SessionStructureValide
    {
        try {
            $sessionStructureComplementaire->dehistoriser();
            $this->getEntityManager()->flush($sessionStructureComplementaire);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [SessionStructureValide]", 0, $e);
        }
        return $sessionStructureComplementaire;
    }

    /**
     * @param SessionStructureValide $sessionStructureComplementaire
     * @return SessionStructureValide
     */
    public function delete(SessionStructureValide $sessionStructureComplementaire): SessionStructureValide
    {
        try {
            $this->getEntityManager()->remove($sessionStructureComplementaire);
            $this->getEntityManager()->flush($sessionStructureComplementaire);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [SessionStructureValide]", 0, $e);
        }
        return $sessionStructureComplementaire;
    }
}