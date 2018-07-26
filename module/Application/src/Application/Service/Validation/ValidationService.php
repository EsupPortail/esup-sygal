<?php

namespace Application\Service\Validation;

use Application\Entity\Db\Individu;
use Application\Entity\Db\Repository\ValidationRepository;
use Application\Entity\Db\These;
use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Validation;
use Application\Entity\Db\VSitu\DepotVersionCorrigeeValidationDirecteur;
use Application\Service\BaseService;
use Application\Service\Individu\IndividuServiceAwareInterface;
use Application\Service\Individu\IndividuServiceAwareTrait;
use Application\Service\UserContextServiceAwareInterface;
use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query\Expr\Join;
use UnicaenApp\Exception\LogicException;
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
     * Fetch le type de validation spécifié.
     *
     * @param string $code
     * @return null|TypeValidation
     */
    public function getTypeValidation($code)
    {
        return $this->entityManager->getRepository(TypeValidation::class)->findOneBy(['code' => $code]);
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
     * @return Validation
     */
    public function validateRdvBu(These $these)
    {
        $v = new Validation($this->getTypeValidation(TypeValidation::CODE_RDV_BU), $these);

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
            ->andWhere('pasHistorise(v) = 1')
            ->setParameter('these', $these)
            ->setParameter('tvcode', TypeValidation::CODE_RDV_BU);
        /** @var Validation $v */
        $v = $qb->getQuery()->getOneOrNullResult();

        if (!$v) {
            throw new RuntimeException(
                sprintf("Aucune validation de type '%s' trouvée pour la thèse %s", TypeValidation::CODE_RDV_BU, $these));
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
            $this->getTypeValidation(TypeValidation::CODE_DEPOT_THESE_CORRIGEE),
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
    public function validateVersionPapierCorrigee(These $these)
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->getTypeValidation(TypeValidation::CODE_VERSION_PAPIER_CORRIGEE),
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
     * @return Validation
     */
    public function validateCorrectionThese(These $these)
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->getTypeValidation(TypeValidation::CODE_CORRECTION_THESE),
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
     * @return DepotVersionCorrigeeValidationDirecteur[]
     */
    public function getValidationsAttenduesPourCorrectionThese(These $these)
    {
        $qb = $this->getEntityManager()->getRepository(DepotVersionCorrigeeValidationDirecteur::class)->createQueryBuilder('va')
            ->addSelect('t, i')
            ->join('va.these', 't', Join::WITH, 't = :these')
            ->join('va.individu', 'i')
            ->andWhere('va.valide = 0')
            ->setParameter('these', $these);

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    public function validatePageDeCouverture($these)
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->getTypeValidation(TypeValidation::CODE_PAGE_DE_COUVERTURE),
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

    public function validatePropositionSoutenance($these)
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->getTypeValidation(TypeValidation::CODE_PROPOSITION_SOUTENANCE),
            $these,
            $individu);

        $this->entityManager->persist($v);
        try {
            $this->entityManager->flush($v);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Erreur lors de l'enregistrement de la validation en bdd", null, $e);
        }
    }

    public function unvalidatePropositionSoutenance($these)
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_PROPOSITION_SOUTENANCE)
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

    /**
     * @var These $these
     * @var Individu $individu
     * @return Validation
     */
    public function findValidationPropositionSoutenanceByTheseAndIndividu($these, $individu) {
        $qb = $this->getRepository()->createQueryBuilder("v")
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_PROPOSITION_SOUTENANCE)
            ->andWhereNotHistorise()
            ->andWhere("v.individu = :individu")
            ->setParameter("individu", $individu);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException("Plusieurs validations pour un même acteur et une même thèse.");
        }
        return $result;
    }

    /**
     * @var These $these
     * @return Validation[]
     */
    public function findValidationPropositionSoutenanceByThese($these) {
        $qb = $this->getRepository()->createQueryBuilder("v")
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_PROPOSITION_SOUTENANCE)
            ->andWhereNotHistorise()
            ;
        $result = $qb->getQuery()->getResult();
        return $result;
    }

    /**
     * @param Validation $validation
     */
    public function historise($validation) {
        $validation->historiser();
        try {
            $this->getEntityManager()->flush($validation);
        } catch (OptimisticLockException $e) {
            throw new RuntimeException("Un problème est survenu lors de l'historisation");
        }
    }


}