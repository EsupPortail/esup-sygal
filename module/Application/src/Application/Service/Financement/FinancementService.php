<?php

namespace Application\Service\Financement;

use Application\Entity\Db\OrigineFinancement;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FinancementService
{
    use EntityManagerAwareTrait;

    /**
     * @param string $order
     * @param bool $cacheable
     * @return OrigineFinancement[]
     */
    public function getOriginesFinancements($order = null, $cacheable = false)
    {
        $qb = $this->getEntityManager()->getRepository(OrigineFinancement::class)->createQueryBuilder('origine');

        if ($order) {
            $qb = $qb->orderBy('origine.' . $order);
        }

        $qb->setCacheable($cacheable);

        return $qb->getQuery()->getResult();
    }
}