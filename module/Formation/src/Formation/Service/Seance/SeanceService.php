<?php

namespace Formation\Service\Seance;

use DateTime;
use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Seance;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class SeanceService {
    use EntityManagerAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Seance $seance
     * @return Seance
     */
    public function create(Seance $seance) : Seance
    {
        try {
            $this->getEntityManager()->persist($seance);
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Seance]",0, $e);
        }
        return $seance;
    }

    /**
     * @param Seance $seance
     * @return Seance
     */
    public function update(Seance $seance) : Seance
    {
        try {
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Seance]",0, $e);
        }
        return $seance;
    }

    /**
     * @param Seance $seance
     * @return Seance
     */
    public function historise(Seance $seance) : Seance
    {
        try {
            $seance->historiser();
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Seance]",0, $e);
        }
        return $seance;
    }

    /**
     * @param Seance $seance
     * @return Seance
     */
    public function restore(Seance $seance) : Seance
    {
        try {
            $seance->dehistoriser();
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Seance]",0, $e);
        }
        return $seance;
    }

    /**
     * @param Seance $seance
     * @return Seance
     */
    public function delete(Seance $seance) : Seance
    {
        try {
            $this->getEntityManager()->remove($seance);
            $this->getEntityManager()->flush($seance);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Seance]",0, $e);
        }
        return $seance;
    }

    /** FACADE ********************************************************************************************************/
}