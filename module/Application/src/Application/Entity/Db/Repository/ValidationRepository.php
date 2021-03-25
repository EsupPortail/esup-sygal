<?php

namespace Application\Entity\Db\Repository;

use Application\Entity\Db\Individu;
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
    public function findValidationByCodeAndThese(string $code, These $these) : array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('t = :these')
            ->andWhere('tv.code = :code')
            ->andWhere('1 = pasHistorise(v)')
            ->setParameter('these', $these)
            ->setParameter('code', $code);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param These $these
     * @param string $code
     * @param Individu $individu
     * @return Validation[]
     */
    public function findValidationByTheseAndCodeAndIndividu(These $these, string $code, Individu $individu) : array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('tv.code = :code')
            ->andWhere('v.individu = :individu')
            ->andWhere('v.these = :these')
            ->andWhere('1 = pasHistorise(v)')
            ->setParameter('code', $code)
            ->setParameter('individu', $individu)
            ->setParameter('these', $these)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param These  $these
     * @return Validation[]
     */
    public function findValidationsByThese(These $these) : array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('t = :these')
            ->andWhere('1 = pasHistorise(v)')
            ->setParameter('these', $these)
        ;
        return $qb->getQuery()->getResult();
    }

}