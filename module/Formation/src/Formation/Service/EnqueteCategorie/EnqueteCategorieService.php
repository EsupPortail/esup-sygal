<?php

namespace Formation\Service\EnqueteCategorie;

use DateTime;
use Doctrine\ORM\ORMException;
use Formation\Entity\Db\EnqueteCategorie;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class EnqueteCategorieService {
    use EntityManagerAwareTrait;

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param EnqueteCategorie $question
     * @return EnqueteCategorie
     */
    public function create(EnqueteCategorie $question) : EnqueteCategorie
    {
        try {
            $this->getEntityManager()->persist($question);
            $this->getEntityManager()->flush($question);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteCategorie]",0, $e);
        }
        return $question;
    }

    /**
     * @param EnqueteCategorie $question
     * @return EnqueteCategorie
     */
    public function update(EnqueteCategorie $question) : EnqueteCategorie
    {
        try {
            $this->getEntityManager()->flush($question);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteCategorie]",0, $e);
        }
        return $question;
    }

    /** (todo ...)
     * @param EnqueteCategorie $question
     * @return EnqueteCategorie
     */
    public function historise(EnqueteCategorie $question) : EnqueteCategorie
    {
        try {
            $question->setHistoDestruction(new DateTime());
            $this->getEntityManager()->flush($question);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteCategorie]",0, $e);
        }
        return $question;
    }

    /**
     * @param EnqueteCategorie $question
     * @return EnqueteCategorie
     */
    public function restore(EnqueteCategorie $question) : EnqueteCategorie
    {
        try {
            $question->setHistoDestructeur(null);
            $question->setHistoDestruction(null);
            $this->getEntityManager()->flush($question);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteCategorie]",0, $e);
        }
        return $question;
    }

    /**
     * @param EnqueteCategorie $question
     * @return EnqueteCategorie
     */
    public function delete(EnqueteCategorie $question) : EnqueteCategorie
    {
        try {
            $this->getEntityManager()->remove($question);
            $this->getEntityManager()->flush($question);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteCategorie]",0, $e);
        }
        return $question;
    }

    /** FACADE ********************************************************************************************************/

    public function getCategoriesAsOptions() : array
    {
        /** @var EnqueteCategorie $categories */
        $categories = $this->getEntityManager()->getRepository(EnqueteCategorie::class)->findAll();
        $options = [];
        foreach ($categories as $categorie) {
            $options[$categorie->getId()] = $categorie->getLibelle();
        }
        return $options;
    }

    public function getRepository()
    {
        return $this->getEntityManager()->getRepository(EnqueteCategorie::class);
    }
}