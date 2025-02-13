<?php

namespace Validation\Entity\Db\Repository;

use Doctrine\ORM\NonUniqueResultException;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;
use RuntimeException;
use Validation\Entity\Db\ValidationHDR;
use Validation\QueryBuilder\ValidationHDRQueryBuilder;

/**
 * @method ValidationHDRQueryBuilder createQueryBuilder($alias, $indexBy = null)
 */
class ValidationHDRRepository extends AbstractValidationEntityRepository
{
    protected string $queryBuilderClassName = ValidationHDRQueryBuilder::class;

    /**
     * @param string $code
     * @param HDR $hdr
     * @return ValidationHDR[]
     */
    public function findValidationByCodeAndHDR(string $code, HDR $hdr): array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('t = :hdr')
            ->andWhere('tv.code = :code')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('hdr', $hdr)
            ->setParameter('code', $code);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param HDR $hdr
     * @param string $code
     * @param Individu $individu
     * @return ValidationHDR|null
     */
    public function findValidationByHDRAndCodeAndIndividu(HDR $hdr, string $code, Individu $individu): ?ValidationHDR
    {
        $qb = $this->createQueryBuilder('v')
            ->where('tv.code = :code')
            ->andWhere('v.individu = :individu')
            ->andWhere('v.hdr = :hdr')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('code', $code)
            ->setParameter('individu', $individu)
            ->setParameter('hdr', $hdr);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie rencontrée : plusieurs validations trouvées !", null, $e);
        }
    }

    /**
     * @param HDR $hdr
     * @return ValidationHDR[]
     */
    public function findValidationsByHDR(HDR $hdr): array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('t = :hdr')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('hdr', $hdr);
        return $qb->getQuery()->getResult();
    }

}