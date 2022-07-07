<?php

namespace Formation\Service\EnqueteQuestion;

use Doctrine\ORM\ORMException;
use Formation\Entity\Db\EnqueteQuestion;
use Formation\Entity\Db\Repository\EnqueteQuestionRepository;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class EnqueteQuestionService {
    use EntityManagerAwareTrait;

    /** @return EnqueteQuestionRepository */
    public function getRepository() : EnqueteQuestionRepository
    {
        /** @var EnqueteQuestionRepository $repo */
        $repo =  $this->getEntityManager()->getRepository(EnqueteQuestion::class);
        return $repo;
    }

    /** GESTION DES ENTITES *******************************************************************************************/

    /**
     * @param EnqueteQuestion $question
     * @return EnqueteQuestion
     */
    public function create(EnqueteQuestion $question) : EnqueteQuestion
    {
        try {
            $this->getEntityManager()->persist($question);
            $this->getEntityManager()->flush($question);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteQuestion]",0, $e);
        }
        return $question;
    }

    /**
     * @param EnqueteQuestion $question
     * @return EnqueteQuestion
     */
    public function update(EnqueteQuestion $question) : EnqueteQuestion
    {
        try {
            $this->getEntityManager()->flush($question);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteQuestion]",0, $e);
        }
        return $question;
    }

    /**
     * @param EnqueteQuestion $question
     * @return EnqueteQuestion
     */
    public function historise(EnqueteQuestion $question) : EnqueteQuestion
    {
        try {
            $question->historiser();
            $this->getEntityManager()->flush($question);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteQuestion]",0, $e);
        }
        return $question;
    }

    /**
     * @param EnqueteQuestion $question
     * @return EnqueteQuestion
     */
    public function restore(EnqueteQuestion $question) : EnqueteQuestion
    {
        try {
            $question->dehistoriser();
            $this->getEntityManager()->flush($question);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteQuestion]",0, $e);
        }
        return $question;
    }

    /**
     * @param EnqueteQuestion $question
     * @return EnqueteQuestion
     */
    public function delete(EnqueteQuestion $question) : EnqueteQuestion
    {
        try {
            $this->getEntityManager()->remove($question);
            $this->getEntityManager()->flush($question);
        } catch (ORMException $e) {
            throw new RuntimeException("Un problème est survnue en base pour une entité [EnqueteQuestion]",0, $e);
        }
        return $question;
    }

    /** FACADE ********************************************************************************************************/

}