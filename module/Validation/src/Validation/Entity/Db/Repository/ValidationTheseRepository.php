<?php

namespace Validation\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
<<<<<<<< HEAD:module/Validation/src/Validation/Entity/Db/Repository/ValidationTheseRepository.php
========
use HDR\Entity\Db\HDR;
>>>>>>>> 7e4e27319 (Nouveau module HDR):module/Validation/src/Validation/Entity/Db/Repository/ValidationRepository.php
use Individu\Entity\Db\Individu;
use RuntimeException;
use These\Entity\Db\These;
use Validation\Entity\Db\ValidationThese;
<<<<<<<< HEAD:module/Validation/src/Validation/Entity/Db/Repository/ValidationTheseRepository.php
use Validation\QueryBuilder\ValidationTheseQueryBuilder;
========
use Validation\QueryBuilder\ValidationQueryBuilder;
>>>>>>>> 7e4e27319 (Nouveau module HDR):module/Validation/src/Validation/Entity/Db/Repository/ValidationRepository.php

/**
 * @method ValidationTheseQueryBuilder createQueryBuilder($alias, $indexBy = null)
 */
class ValidationTheseRepository extends DefaultEntityRepository
{
<<<<<<<< HEAD:module/Validation/src/Validation/Entity/Db/Repository/ValidationTheseRepository.php
    protected string $queryBuilderClassName = ValidationTheseQueryBuilder::class;

    /**
     * @param string $code
     * @param These $these
========
    protected string $queryBuilderClassName = ValidationQueryBuilder::class;

    /**
>>>>>>>> 7e4e27319 (Nouveau module HDR):module/Validation/src/Validation/Entity/Db/Repository/ValidationRepository.php
     * @return ValidationThese[]
     */
    public function findValidationByCodeAndThese(string $code, These $these): array
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
<<<<<<<< HEAD:module/Validation/src/Validation/Entity/Db/Repository/ValidationTheseRepository.php
     * @param These $these
     * @param string $code
     * @param Individu $individu
     * @return ValidationThese|null
     */
    public function findValidationByTheseAndCodeAndIndividu(These $these, string $code, Individu $individu): ?ValidationThese
========
     * @return ValidationThese|null
     */
    public function findValidationByTheseAndCodeAndIndividu(These $these, string $code, Individu $individu) : ?ValidationThese
>>>>>>>> 7e4e27319 (Nouveau module HDR):module/Validation/src/Validation/Entity/Db/Repository/ValidationRepository.php
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
<<<<<<<< HEAD:module/Validation/src/Validation/Entity/Db/Repository/ValidationTheseRepository.php
     * @param These $these
========
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
>>>>>>>> 7e4e27319 (Nouveau module HDR):module/Validation/src/Validation/Entity/Db/Repository/ValidationRepository.php
     * @return ValidationThese[]
     */
    public function findValidationsByThese(These $these): array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('t = :these')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('these', $these);
        return $qb->getQuery()->getResult();
    }

}