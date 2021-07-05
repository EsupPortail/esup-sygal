<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Inscription;
use Formation\Entity\Db\Presence;
use Formation\Entity\Db\Seance;
use Formation\Entity\Db\Session;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class PresenceRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Presence|null
     */
    public function getRequestedPresence(AbstractActionController $controller, string $param = 'presence') : ?Presence
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Presence|null $presence */
        $presence = $this->find($id);
        return $presence;
    }

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    public function createQB(string $alias) : QueryBuilder
    {
        $qb = $this->createQueryBuilder($alias)
            ->leftJoin($alias.".inscription", "inscription")->addSelect("inscription")
            ->leftJoin($alias.".seance", "seance")->addSelect("seance")
        ;
        return $qb;
    }

    /**
     * @param Inscription $inscription
     * @param Seance $seance
     * @return Presence|null
     */
    public function findPresenceByInscriptionAndSeance(Inscription $inscription, Seance $seance) : ?Presence
    {
        $qb = $this->createQB('presence')
            ->andWhere('presence.inscription = :inscription')
            ->setParameter('inscription', $inscription)
            ->andWhere('presence.seance = :seance')
            ->setParameter('seance', $seance)
            ->andWhere('presence.histoDestruction IS NULL')
        ;
        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs Presence de renseigner pour l'inscription [".$inscription->getId()."] et la seance [".$seance->getId()."]");
        }
        return $result;
    }

    /**
     * @param Session $session
     * @return Presence[]
     */
    public function findPresencesBySession(Session $session) : array
    {
        $qb = $this->createQB('presence')
            ->andWhere('seance.session = :session')
            ->setParameter('session', $session)
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Inscription $inscription
     * @return Presence[]
     */
    public function findPresencesByInscription(Inscription $inscription) : array
    {
        $qb = $this->createQB('presence')
            ->andWhere('presence.inscription = :inscription')
            ->setParameter('inscription', $inscription)
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }
}