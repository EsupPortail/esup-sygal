<?php

namespace Validation\Entity\Db\Repository;

use Application\Entity\Db\Repository\DefaultEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Formation\Entity\Db\FormationExterne;
use Individu\Entity\Db\Individu;
use RuntimeException;
use Validation\Entity\Db\ValidationFormationExterne;
use Validation\QueryBuilder\ValidationFormationExterneQueryBuilder;

class ValidationFormationExterneRepository extends DefaultEntityRepository
{
    protected string $queryBuilderClassName = ValidationFormationExterneQueryBuilder::class;

    /**
     * @param string $code
     * @param FormationExterne $formationExterne
     * @return ValidationFormationExterne[]
     */
    public function findValidationsByCodeAndFormationExterne(string $code, FormationExterne $formationExterne): array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('fe = :formationExterne')
            ->andWhere('tv.code = :code')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('formationExterne', $formationExterne)
            ->setParameter('code', $code);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param FormationExterne $formationExterne
     * @param string $code
     * @param Individu $individu
     * @return ValidationFormationExterne|null
     */
    public function findValidationByFormationExterneAndCodeAndIndividu(FormationExterne $formationExterne, string $code, Individu $individu) : ?ValidationFormationExterne
    {
        $qb = $this->createQueryBuilder('v')
            ->where('tv.code = :code')
            ->andWhere('v.individu = :individu')
            ->andWhere('v.formationExterne = :formationExterne')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('code', $code)
            ->setParameter('individu', $individu)
            ->setParameter('formationExterne', $formationExterne);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie rencontrée : plusieurs validations trouvées !", null, $e);
        }
    }

    /**
     * @param FormationExterne $formationExterne
     * @param string $code
     * @return ValidationFormationExterne|null
     */
    public function findValidationByFormationExterneAndCode(FormationExterne $formationExterne, string $code) : ?ValidationFormationExterne
    {
        $qb = $this->createQueryBuilder('v')
            ->where('tv.code = :code')
            ->andWhere('v.formationExterne = :formationExterne')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('code', $code)
            ->setParameter('formationExterne', $formationExterne);

        try {
            return $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Anomalie rencontrée : plusieurs validations trouvées !", null, $e);
        }
    }

    /**
     * @return ValidationFormationExterne[]
     */
    public function findValidationsByFormationExterne(FormationExterne $formationExterne): array
    {
        $qb = $this->createQueryBuilder('v')
            ->where('t = :formationExterne')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('formationExterne', $formationExterne);
        return $qb->getQuery()->getResult();
    }
}