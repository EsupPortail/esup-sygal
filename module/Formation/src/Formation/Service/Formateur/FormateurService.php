<?php

namespace Formation\Service\Formateur;

use DateTime;
use Doctrine\ORM\ORMException;
use Formation\Entity\Db\Formateur;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FormateurService {
    use EntityManagerAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param Formateur $formateur
     * @return Formateur
     */
    public function create(Formateur $formateur) : Formateur
    {
        try {
            $this->getEntityManager()->persist($formateur);
            $this->getEntityManager()->flush($formateur);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formateur]",0, $e);
        }
        return $formateur;
    }

    /**
     * @param Formateur $formateur
     * @return Formateur
     */
    public function update(Formateur $formateur) : Formateur
    {
        try {
            $this->getEntityManager()->flush($formateur);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formateur]",0, $e);
        }
        return $formateur;
    }

    /** (todo ...)
     * @param Formateur $formateur
     * @return Formateur
     */
    public function historise(Formateur $formateur) : Formateur
    {
        try {
            $formateur->setHistoDestruction(new DateTime());
            $this->getEntityManager()->flush($formateur);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formateur]",0, $e);
        }
        return $formateur;
    }

    /**
     * @param Formateur $formateur
     * @return Formateur
     */
    public function restore(Formateur $formateur) : Formateur
    {
        try {
            $formateur->setHistoDestructeur(null);
            $formateur->setHistoDestruction(null);
            $this->getEntityManager()->flush($formateur);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formateur]",0, $e);
        }
        return $formateur;
    }

    /**
     * @param Formateur $formateur
     * @return Formateur
     */
    public function delete(Formateur $formateur) : Formateur
    {
        try {
            $this->getEntityManager()->remove($formateur);
            $this->getEntityManager()->flush($formateur);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [Formateur]",0, $e);
        }
        return $formateur;
    }

    /** FACADE ********************************************************************************************************/
}