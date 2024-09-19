<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Source;
use Doctrine\ORM\NonUniqueResultException;

class SourceRepository extends DefaultEntityRepository
{
    public function findOneByCode(string $code): ?Source
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.etablissement', 'e')->addSelect('e')
            ->where('s.code = :code')->setParameter('code', $code);

        /** @var Source $source */
        try {
            $source = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new \RuntimeException("Anomalie : plusieurs Sources existent avec ce code", null, $e);
        }

        return $source;
    }
}