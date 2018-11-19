<?php

namespace Application\Service\Financement;

use Application\Entity\Db\OrigineFinancement;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FinancementService {
    use EntityManagerAwareTrait;

    /**
     * @param string $order
     * @return OrigineFinancement[]
     */
    public function getOriginesFinancements($order = null)
    {
        $qb = $this->getEntityManager()->getRepository(OrigineFinancement::class)->createQueryBuilder('origine');

        if ($order) $qb = $qb->orderBy('origine.' . $order);

        $result = $qb->getQuery()->getResult();
        return $result;
    }
}