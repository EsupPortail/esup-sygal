<?php

namespace Validation\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use HDR\Entity\Db\HDR;
use Individu\Entity\Db\Individu;
use RuntimeException;
use These\Entity\Db\These;
use Validation\Entity\Db\ValidationThese;
use Validation\QueryBuilder\ValidationQueryBuilder;

/**
 * @method ValidationQueryBuilder createQueryBuilder($alias, $indexBy = null)
 */
class ValidationRepository extends DefaultEntityRepository
{
    protected string $queryBuilderClassName = ValidationQueryBuilder::class;

    /**
     * @return ValidationThese[]
     */
    public function findValidationByCodeAndThese(string $code, These $these) : array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('t = :these')
            ->andWhere('tv.code = :code')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('these', $these)
            ->setParameter('code', $code);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return ValidationThese|null
     */
    public function findValidationByTheseAndCodeAndIndividu(These $these, string $code, Individu $individu) : ?ValidationThese
    {
        $qb = $this->createQueryBuilder('v')
            ->where('tv.code = :code')
            ->andWhere('v.individu = :individu')
            ->andWhere('v.these = :these')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('code', $code)
            ->setParameter('individu', $individu)
            ->setParameter('these', $these);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie rencontrée : plusieurs validations trouvées !", null, $e);
        }
    }

    /**
     * @param HDR $hdr
     * @param string $code
     * @param Individu $individu
     * @return ValidationThese|null
     */
    public function findValidationByHDRAndCodeAndIndividu(HDR $hdr, string $code, Individu $individu) : ?ValidationThese
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
     * @return ValidationThese[]
     */
    public function findValidationsByThese(These $these) : array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('t = :these')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('these', $these)
        ;
        return $qb->getQuery()->getResult();
    }

}