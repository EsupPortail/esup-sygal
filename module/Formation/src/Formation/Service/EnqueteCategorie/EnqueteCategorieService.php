<?php

namespace Formation\Service\EnqueteCategorie;

use Doctrine\ORM\ORMException;
use Formation\Entity\Db\EnqueteCategorie;
use Formation\Entity\Db\Repository\EnqueteCategorieRepository;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class EnqueteCategorieService {
    use EntityManagerAwareTrait;

    /** @return EnqueteCategorieRepository */
    public function getRepository() : EnqueteCategorieRepository
    {
        /** @var EnqueteCategorieRepository $repo */
        $repo =  $this->getEntityManager()->getRepository(EnqueteCategorie::class);
        return $repo;
    }

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

    /**
     * @param EnqueteCategorie $question
     * @return EnqueteCategorie
     */
    public function historise(EnqueteCategorie $question) : EnqueteCategorie
    {
        try {
            $question->historiser();
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
            $question->dehistoriser();
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


}