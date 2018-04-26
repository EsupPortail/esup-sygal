<?php

namespace Application\Service;

use Application\Entity\Db\Anomalie;
use Doctrine\ORM\EntityRepository;

class AnomalieService extends BaseService
{
    /**
     * @return EntityRepository
     */
    public function getRepository()
    {
        /** @var EntityRepository $repo */
        $repo = $this->getEntityManager()->getRepository(Anomalie::class);
        return $repo;
    }

    /**
     * @return Anomalie[]
     */
    public function getAnomalies($etablissementId = null)
    {
        $qb = $this->getEntityManager()->getRepository(Anomalie::class)->createQueryBuilder("a")
            ->orderBy("a.tableName, a.sourceCode");
        if ($etablissementId !== null && $etablissementId !== '') {
            $qb = $qb->andWhere("a.etablissementId = :etablissementId")
                ->setParameter("etablissementId", $etablissementId);
        }
        $result = $qb->getQuery()->getResult();
        return $result;
    }
}