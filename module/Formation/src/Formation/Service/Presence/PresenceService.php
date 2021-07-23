<?php

namespace Formation\Service\Presence;

use DateTime;
use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Presence;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class PresenceService {
    use EntityManagerAwareTrait;

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

    /** (todo ...)
     * @param Presence $presence
     * @return Presence
     */
    public function historise(Presence $presence) : Presence
    {
        try {
            $presence->setHistoDestruction(new DateTime());
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
            $presence->setHistoDestructeur(null);
            $presence->setHistoDestruction(null);
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

        /** @var Presence[] $presences */
        $presences = $this->getEntityManager()->getRepository(Presence::class)->findPresencesByInscription($inscription);
        foreach ($presences as $presence) {
            if ($presence->isPresent()) {
                $duree += $presence->getSeance()->getDuree();
            }
        }
        return $duree;
    }


}