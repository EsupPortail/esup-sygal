<?php

namespace Formation\Entity\Db\Repository;

use Application\Entity\Db\Doctorant;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Session;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class InscriptionRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Inscription|null
     */
    public function getRequestedInscription(AbstractActionController $controller, string $param = 'inscription') : ?Inscription
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Inscription|null $inscription */
        $inscription = $this->find($id);
        return $inscription;
    }

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    public function createQB(string $alias) : QueryBuilder
    {
        $qb = $this->createQueryBuilder($alias)
            ->join($alias.".session", "session")->addSelect("session")
            ->join("session.module", "module")->addSelect("module")
            ->join($alias.".doctorant", "doctorant")->addSelect("doctorant")
        ;
        return $qb;
    }

    /**
     * @param Doctorant $doctorant
     * @return array
     */
    public function findInscriptionsByDoctorant(Doctorant $doctorant) : array
    {
        $qb = $this->createQB('inscription')
            ->andWhere('inscription.doctorant = :doctorant')
            ->setParameter('doctorant', $doctorant)
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }
}