<?php

namespace Application\Service\Validation;

use Individu\Entity\Db\Individu;
use Application\Entity\Db\Repository\ValidationRepository;
use These\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Validation;
use Application\Entity\Db\VSitu\DepotVersionCorrigeeValidationPresident;
use Application\Service\BaseService;
use Individu\Service\IndividuServiceAwareInterface;
use Individu\Service\IndividuServiceAwareTrait;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\RuntimeException;

class ValidationService extends BaseService
    implements UserContextServiceAwareInterface, IndividuServiceAwareInterface
{
    use UserContextServiceAwareTrait;
    use IndividuServiceAwareTrait;

    /**
     * @return ValidationRepository
     */
    public function getRepository()
    {
        return $this->entityManager->getRepository(Validation::class);
    }

    /**
     * Fetch le type de validation spécifié par son code.
     *
     * @param string $code
     * @return TypeValidation
     */
    public function findTypeValidationByCode(string $code): TypeValidation
    {
        /** @var TypeValidation $type */
        $type = $this->entityManager->getRepository(TypeValidation::class)->findOneBy(['code' => $code]);
        if ($type === null) {
            throw new RuntimeException("Type de validation introuvable avec ce code : " . $code);
        }

        return $type;
    }

    /**
     * Fetch le type de validation spécifié par son id.
     *
     * @param int $id
     * @return TypeValidation
     */
    public function findTypeValidationById(int $id): TypeValidation
    {
        /** @var TypeValidation $type */
        $type = $this->entityManager->getRepository(TypeValidation::class)->find($id);
        if ($type === null) {
            throw new RuntimeException("Type de validation introuvable avec cet id : " . $id);
        }

        return $type;
    }

    /**
     * Recherche s'il existe des validations RDV BU historisées.
     *
     * @param These $these
     * @return bool
     */
    public function existsValidationRdvBuHistorisee(These $these)
    {
        return (bool) $these->getValidation(TypeValidation::CODE_RDV_BU, true);
    }

    /**
     * @param These $these
     * @param Individu $createur
     * @return Validation
     */
    public function validateRdvBu(These $these, Individu $createur)
    {
        $v = new Validation($this->findTypeValidationByCode(TypeValidation::CODE_RDV_BU), $these, $createur);

        $this->entityManager->persist($v);
        $this->entityManager->flush($v);

        return $v;
    }

    /**
     * @param These $these
     */
    public function unvalidateRdvBu(These $these)
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->andWhere('tv.code = :tvcode')
            ->andWhere('v.these = :these')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('these', $these)
            ->setParameter('tvcode', TypeValidation::CODE_RDV_BU);
        /** @var Validation $v */
        $v = $qb->getQuery()->getOneOrNullResult();

        if (!$v) {
            throw new RuntimeException(
                sprintf("Aucune validation de type '%s' trouvée pour la thèse %s", TypeValidation::CODE_RDV_BU, $these->getId()));
        }

        $v->historiser();

        $this->getEntityManager()->flush($v);
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function validateDepotTheseCorrigee(These $these)
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->findTypeValidationByCode(TypeValidation::CODE_DEPOT_THESE_CORRIGEE),
            $these,
            $individu);

        $this->entityManager->persist($v);
        try {
            $this->entityManager->flush($v);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd", null, $e);
        }

        return $v;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function validateVersionPapierCorrigee(These $these)
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->findTypeValidationByCode(TypeValidation::CODE_VERSION_PAPIER_CORRIGEE),
            $these,
            $individu);

        $this->entityManager->persist($v);
        $this->entityManager->flush($v);

        return $v;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function unvalidateDepotTheseCorrigee(These $these)
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_DEPOT_THESE_CORRIGEE)
            ->andWhereNotHistorise();
        try {
            /** @var Validation $v */
            $v = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(
                sprintf("Plusieures validations non historisées de type '%s' trouvées pour la thèse '%s'", $type, $these));
        }

        if ($v !== null) {
            $v->historiser();
            try {
                $this->getEntityManager()->flush($v);
            } catch (OptimisticLockException $e) {
                throw new RuntimeException("Erreur rencontrée lors de l'enregistrement en bdd", null, $e);
            }
        }

        return $v;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function validateCorrectionThese(These $these)
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->findTypeValidationByCode(TypeValidation::CODE_CORRECTION_THESE),
            $these,
            $individu);

        $this->entityManager->persist($v);
        $this->entityManager->flush($v);

        return $v;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function unvalidateCorrectionThese(These $these)
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_CORRECTION_THESE)
            ->andWhereNotHistorise();
        /** @var Validation $v */
        $v = $qb->getQuery()->getOneOrNullResult();

        if (!$v) {
            throw new RuntimeException(
                sprintf("Aucune validation de type '%s' trouvée pour la thèse %s", $type, $these));
        }

        $v->historiser();

        $this->getEntityManager()->flush($v);

        return $v;
    }

    /**
     * @param These $these
     * @return DepotVersionCorrigeeValidationPresident[]
     */
    public function getValidationsAttenduesPourCorrectionThese(These $these): array
    {
        $qb = $this->getEntityManager()->getRepository(DepotVersionCorrigeeValidationPresident::class)->createQueryBuilder('va')
            ->addSelect('t, i')
            ->join('va.these', 't', Join::WITH, 't = :these')
            ->join('va.individu', 'i')
            ->andWhere('va.valide = 0')
            ->setParameter('these', $these);

        return $qb->getQuery()->getResult();
    }

    public function validatePageDeCouverture($these)
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->findTypeValidationByCode(TypeValidation::CODE_PAGE_DE_COUVERTURE),
            $these,
            $individu);

        $this->entityManager->persist($v);
        try {
            $this->entityManager->flush($v);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de la validation en bdd", null, $e);
        }
    }

    public function unvalidatePageDeCouverture($these)
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_PAGE_DE_COUVERTURE)
            ->andWhereNotHistorise();
        /** @var Validation $v */
        try {
            $v = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(
                sprintf("Anomalie: plus d'une validation de type '%s' trouvée pour la thèse %s", $type, $these));
        }

        if (!$v) {
            throw new RuntimeException(
                sprintf("Aucune validation de type '%s' trouvée pour la thèse %s", $type, $these));
        }

        $v->historiser();

        try {
            $this->getEntityManager()->flush($v);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'historisation de la validation en bdd", null, $e);
        }

        return $v;
    }
}