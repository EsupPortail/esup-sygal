<?php

namespace Formation\Entity\Db\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Formation\Entity\Db\Justificatif;
use Formation\Entity\Db\JustificatifSeance;
use Formation\Entity\Db\Seance;
use Laminas\Mvc\Controller\AbstractActionController;
use UnicaenApp\Service\EntityManagerAwareTrait;

class JustificatifSeanceRepository extends EntityRepository
{
    use EntityManagerAwareTrait;

    /**
     * @param AbstractActionController $controller
     * @param string $param
     * @return JustificatifSeance|null
     */
    public function getRequestedJustificatifSeance(AbstractActionController $controller, string $param = 'justificatifSeance') : ?JustificatifSeance
    {
        $id = $controller->params()->fromRoute($param);
        /** @var JustificatifSeance|null $justificatifSeance */
        $justificatifSeance = $this->find($id);
        return $justificatifSeance;
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

    public function findOneByJustificatifAndSeance(Justificatif $justificatif, Seance $seance)
    {
        return $this->findOneBy([
            'justificatif' => $justificatif,
            'seance' => $seance,
        ]);
    }
}