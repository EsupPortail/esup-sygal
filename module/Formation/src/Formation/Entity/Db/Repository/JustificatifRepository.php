<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Justificatif;
use Formation\Entity\Db\Seance;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;

class JustificatifRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Justificatif|null
     */
    public function getRequestedJustificatif(AbstractActionController $controller, string $param = 'justificatif') : ?Justificatif
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Justificatif|null $justificatif */
        $justificatif = $this->find($id);
        return $justificatif;
    }

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    public function createQB(string $alias) : QueryBuilder
    {
        $qb = $this->createQueryBuilder($alias)
            ->leftJoin($alias.".inscription", "inscription")->addSelect("inscription")
            ->leftJoin($alias.".justificatifSeances", "justificatifSeances")->addSelect("justificatifSeances")
            ->leftJoin("justificatifSeances.seance", "seance")->addSelect("seance")
        ;
        return $qb;
    }

    /**
     * @param Inscription $inscription
     * @param Seance $seance
     * @return Justificatif|null
     */
    public function findJustificatifByInscriptionAndSeance(Inscription $inscription, Seance $seance) : ?Justificatif
    {
        $qb = $this->createQB('justificatif')
            ->andWhere('justificatif.inscription = :inscription')
            ->setParameter('inscription', $inscription)
            ->andWhere('seance = :seance')
            ->setParameter('seance', $seance)
            ->andWhere('justificatif.histoDestruction IS NULL')
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Justificatif de renseigner pour l'inscription [".$inscription->getId()."] et la seance [".$seance->getId()."]");
        }
        return $result;
    }

    /**
     * @param Inscription $inscription
     * @return Justificatif[]
     */
    public function findJustificatifsByInscription(Inscription $inscription) : array
    {
        $qb = $this->createQB('justificatif')
            ->andWhere('justificatif.inscription = :inscription')
            ->setParameter('inscription', $inscription)
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }
}