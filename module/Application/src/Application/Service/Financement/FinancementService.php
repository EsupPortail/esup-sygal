<?php

namespace Application\Service\Financement;

use Application\Entity\Db\OrigineFinancement;
use UnicaenApp\Service\EntityManagerAwareTrait;

class FinancementService
{
    use EntityManagerAwareTrait;

    /**
     * @param string|null $order
     * @param bool $cacheable
     * @return OrigineFinancement[]
     */
    public function findOriginesFinancements(string $order = null, bool $cacheable = false): array
    {
        $qb = $this->getRepositoryOrigineFinancement()->createQueryBuilder('origine');

        if ($order) {
            $qb = $qb->orderBy('origine.' . $order);
        }

        $qb->setCacheable($cacheable);

        return $qb->getQuery()->getResult();
    }

    public function getOrigineFinancementByCode(string $code): OrigineFinancement
    {
        /** @var OrigineFinancement $of */
        $of = $this->getRepositoryOrigineFinancement()->findOneBy(['code' => $code]);

        return $of;
    }

    protected function getRepositoryOrigineFinancement()
    {
        return $this->getEntityManager()->getRepository(OrigineFinancement::class);
    }
}