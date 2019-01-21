<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\These;
use Application\Entity\Db\Validation;
use Application\QueryBuilder\ValidationQueryBuilder;

/**
 * @method ValidationQueryBuilder createQueryBuilder($alias, $indexBy = null)
 */
class ValidationRepository extends DefaultEntityRepository
{
    /**
     * @var string
     */
    protected $queryBuilderClassName = ValidationQueryBuilder::class;

    /**
     * @param string $code
     * @param These  $these
     * @return Validation[]
     */
    public function findValidationByCodeAndThese($code, These $these)
    {
        $qb = $this->createQueryBuilder('v')
            ->where('t = :these')
            ->andWhere('tv.code = :code')
            ->andWhere('1 = pasHistorise(v)')
            ->setParameter('these', $these)
            ->setParameter('code', $code);

        return $qb->getQuery()->getResult();
    }
}