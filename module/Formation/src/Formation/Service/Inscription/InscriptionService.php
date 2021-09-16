<?php

namespace Formation\Service\Inscription;

use DateTime;
use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Inscription;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class InscriptionService {
    use EntityManagerAwareTrait;

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
     * @param Inscription $inscription
     * @return Inscription
     */
    public function historise(Inscription $inscription) : Inscription
    {
        try {
            $inscription->historiser();
            $this->getEntityManager()->flush($inscription);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Inscription]",0, $e);
        }
        return $inscription;
    }

    /**
     * @param Inscription $inscription
     * @return Inscription
     */
    public function restore(Inscription $inscription) : Inscription
    {
        try {
            $inscription->dehistoriser();
            $this->getEntityManager()->flush($inscription);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Inscription]",0, $e);
        }
        return $inscription;
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