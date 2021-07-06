<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\EnqueteReponse;
use Formation\Entity\Db\Inscription;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class EnqueteReponseRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return EnqueteReponse|null
     */
    public function getRequestedEnqueteReponse(AbstractActionController $controller, string $param = 'reponse') : ?EnqueteReponse
    {
        $id = $controller->params()->fromRoute($param);
        /** @var EnqueteReponse|null $reponse */
        $reponse = $this->find($id);
        return $reponse;
    }

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    public function createQB(string $alias) : QueryBuilder
    {
        $qb = $this->createQueryBuilder($alias)
            ->join($alias.".inscription", "inscription")->addSelect("inscription")
            ->join($alias.".question", "question")->addSelect("question")
        ;
        return $qb;
    }

    /**
     * @param Inscription $inscription
     * @return EnqueteReponse[]
     */
    public function findEnqueteReponseByInscription(Inscription $inscription) : array
    {
        $qb = $this->createQB('enquete-reponse')
            ->andWhere('enquete-reponse.inscription  = :inscription')
            ->setParameter('inscription', $inscription)
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

}