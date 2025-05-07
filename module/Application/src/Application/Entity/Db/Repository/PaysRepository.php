<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Pays;
use Doctrine\ORM\NonUniqueResultException;
use RuntimeException;

class PaysRepository extends DefaultEntityRepository
{
    public function findOneByCodeIsoAlpha2(string $codeIsoAlpha2): ?Pays
    {
        try {
            return $this->createQueryBuilder('p')
                ->where('p.codeIsoAlpha2 = :codeIsoAlpha2')->setParameter('codeIsoAlpha2', $codeIsoAlpha2)
                ->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie : plusieurs pays non historisés ont le même code ISO Alpha 2", null, $e);
        }
    }
}