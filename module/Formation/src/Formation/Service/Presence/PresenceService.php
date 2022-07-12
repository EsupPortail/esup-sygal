<?php

namespace Formation\Service\Presence;

use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Presence;
use Formation\Entity\Db\Repository\PresenceRepository;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class PresenceService {
    use EntityManagerAwareTrait;

    /**
     * @return PresenceRepository
     */
    public function getRepository() : PresenceRepository
    {
        /** @var PresenceRepository $repo */
        $repo = $this->entityManager->getRepository(Presence::class);
        return $repo;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Presence $presence
     * @return Presence
     */
    public function create(Presence $presence) : Presence
    {
        try {
            $this->getEntityManager()->persist($presence);
            $this->getEntityManager()->flush($presence);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Presence]",0, $e);
        }
        return $presence;
    }

    /**
     * @param Presence $presence
     * @return Presence
     */
    public function update(Presence $presence) : Presence
    {
        try {
            $this->getEntityManager()->flush($presence);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Presence]",0, $e);
        }
        return $presence;
    }

    /**
     * @param Presence $presence
     * @return Presence
     */
    public function historise(Presence $presence) : Presence
    {
        try {
            $presence->historiser();
            $this->getEntityManager()->flush($presence);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Presence]",0, $e);
        }
        return $presence;
    }

    /**
     * @param Presence $presence
     * @return Presence
     */
    public function restore(Presence $presence) : Presence
    {
        try {
            $presence->dehistoriser();
            $this->getEntityManager()->flush($presence);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Presence]",0, $e);
        }
        return $presence;
    }

    /**
     * @param Presence $presence
     * @return Presence
     */
    public function delete(Presence $presence) : Presence
    {
        try {
            $this->getEntityManager()->remove($presence);
            $this->getEntityManager()->flush($presence);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Presence]",0, $e);
        }
        return $presence;
    }

    /** FACADE ********************************************************************************************************/

    /**
     * @param Inscription $inscription
     * @return float
     */
    public function calculerDureePresence(Inscription $inscription) : float
    {
        $duree = 0;

        $presences = $this->getRepository()->findPresencesByInscription($inscription);
        foreach ($presences as $presence) {
            if ($presence->isPresent()) {
                $duree += $presence->getSeance()->getDuree();
            }
        }
        return $duree;
    }


}