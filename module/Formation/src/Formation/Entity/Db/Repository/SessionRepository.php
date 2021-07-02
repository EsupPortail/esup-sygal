<?php

namespace Formation\Entity\Db\Repository;

use Application\Entity\Db\Doctorant;
use Application\Entity\Db\These;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Etat;
use Formation\Entity\Db\Interfaces\HasTypeInterface;
use Formation\Entity\Db\Session;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Zend\Mvc\Controller\AbstractActionController;

class SessionRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return Session|null
     */
    public function getRequestedSession(AbstractActionController $controller, string $param = 'session') : ?Session
    {
        $id = $controller->params()->fromRoute($param);
        /** @var Session|null $session */
        $session = $this->find($id);
        return $session;
    }

    /**
     * @param string $alias
     * @return QueryBuilder
     */
    public function createQB(string $alias) : QueryBuilder
    {
        $qb = $this->createQueryBuilder($alias)
            ->join($alias.".module", "module")->addSelect("module")
            ->leftJoin($alias.".inscriptions", "inscription")->addSelect("inscription")
            ->leftJoin($alias.".seances", "seance")->addSelect("seance")
            ->leftJoin($alias.".etat", "etat")->addSelect("etat")
        ;
        return $qb;
    }

    /**
     * @param Doctorant $doctorant
     * @return Session[]
     */
    public function findSessionsDisponiblesByDoctorant(Doctorant $doctorant) : array
    {
        $structures = [];
        foreach ($doctorant->getTheses() as $these) {
            if ($these->estNonHistorise() AND $these->getEtatThese() === These::ETAT_EN_COURS) {
                $etablissement  = ($these->getEtablissement())?$these->getEtablissement()->getStructure():null;
                $ecoleDoctorale = ($these->getEcoleDoctorale())?$these->getEcoleDoctorale()->getStructure():null;
                $uniteRecherche = ($these->getUniteRecherche())?$these->getUniteRecherche()->getStructure():null;
                if ($etablissement) $structures[] = $etablissement;
                if ($ecoleDoctorale) $structures[] = $ecoleDoctorale;
                if ($uniteRecherche) $structures[] = $uniteRecherche;
            }
        }

        $qb = $this->createQB('session')
            ->andWhere('session.type = :transversale OR session.typeStructure in (:structures)')
            ->setParameter('transversale', HasTypeInterface::TYPE_TRANSVERSALE)
            ->setParameter('structures', $structures)
            ->andWhere('etat.code = :ouverte OR etat.code = :preparation')
            ->setParameter('ouverte', Etat::CODE_OUVERTE)
            ->setParameter('preparation', Etat::CODE_PREPARATION)
        ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

}