<?php

namespace Formation\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctorant\Entity\Db\Doctorant;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\EnqueteReponse;
use Formation\Entity\Db\Inscription;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Service\EntityManagerAwareTrait;

class EnqueteReponseRepository extends DefaultEntityRepository
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
        return $this->createQueryBuilder($alias)
            ->join($alias.".inscription", "inscription")->addSelect("inscription")
            ->join($alias.".question", "question")->addSelect("question")
        ;
    }

    /**
     * @param Inscription $inscription
     * @return EnqueteReponse[]
     */
    public function findEnqueteReponseByInscription(Inscription $inscription) : array
    {
        $qb = $this->createQB('enquetereponse')
            ->andWhere('enquetereponse.inscription  = :inscription')
            ->setParameter('inscription', $inscription)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Doctorant $doctorant
     * @return EnqueteReponse[]
     */
    public function findEnqueteReponseByDoctorant(Doctorant $doctorant) : array
    {
        $qb = $this->createQB('enquetereponse')
            ->andWhere('inscription.doctorant  = :doctorant')
            ->setParameter('doctorant', $doctorant)
        ;

        return $qb->getQuery()->getResult();
    }

}