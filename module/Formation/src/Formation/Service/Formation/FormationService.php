<?php

namespace Formation\Service\Formation;

use DateTime;
use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Formation;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FormationService {
    use EntityManagerAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Formation $formation
     * @return Formation
     */
    public function create(Formation $formation) : Formation
    {
        try {
            $this->getEntityManager()->persist($formation);
            $this->getEntityManager()->flush($formation);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formation]",0, $e);
        }
        return $formation;
    }

    /**
     * @param Formation $formation
     * @return Formation
     */
    public function update(Formation $formation) : Formation
    {
        try {
            $this->getEntityManager()->flush($formation);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formation]",0, $e);
        }
        return $formation;
    }

    /** (todo ...)
     * @param Formation $formation
     * @return Formation
     */
    public function historise(Formation $formation) : Formation
    {
        try {
            $formation->setHistoDestruction(new DateTime());
            $this->getEntityManager()->flush($formation);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formation]",0, $e);
        }
        return $formation;
    }

    /**
     * @param Formation $formation
     * @return Formation
     */
    public function restore(Formation $formation) : Formation
    {
        try {
            $formation->setHistoDestructeur(null);
            $formation->setHistoDestruction(null);
            $this->getEntityManager()->flush($formation);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formation]",0, $e);
        }
        return $formation;
    }

    /**
     * @param Formation $formation
     * @return Formation
     */
    public function delete(Formation $formation) : Formation
    {
        try {
            $this->getEntityManager()->remove($formation);
            $this->getEntityManager()->flush($formation);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formation]",0, $e);
        }
        return $formation;
    }

    /** FACADE ********************************************************************************************************/
}