<?php

namespace Depot\Service\Validation;

use Application\Entity\Db\TypeValidation;
use Application\Entity\Db\Validation;
use Application\Service\UserContextServiceAwareTrait;
use Application\Service\Validation\ValidationServiceAwareTrait;
use Depot\Entity\Db\VSitu\DepotVersionCorrigeeValidationPresident;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Individu\Entity\Db\Individu;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;

class DepotValidationService
{
    use ValidationServiceAwareTrait;
    use UserContextServiceAwareTrait;

    /**
     * Recherche s'il existe des validations RDV BU historisées.
     *
     * @param These $these
     * @return bool
     */
    public function existsValidationRdvBuHistorisee(These $these): bool
    {
        return (bool) $these->getValidation(TypeValidation::CODE_RDV_BU, true);
    }

    /**
     * @param These $these
     * @param Individu $createur
     * @return Validation
     */
    public function validateRdvBu(These $these, Individu $createur): Validation
    {
        $v = new Validation(
            $this->validationService->findTypeValidationByCode(TypeValidation::CODE_RDV_BU),
            $these,
            $createur
        );

        $this->validationService->saveValidation($v);

        return $v;
    }

    /**
     * @param These $these
     */
    public function unvalidateRdvBu(These $these)
    {
        $qb = $this->validationService->getRepository()->createQueryBuilder('v')
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

        $this->validationService->saveValidation($v);
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function validateDepotTheseCorrigee(These $these): Validation
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->validationService->findTypeValidationByCode(TypeValidation::CODE_DEPOT_THESE_CORRIGEE),
            $these,
            $individu);

        $this->validationService->saveValidation($v);

        return $v;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function validateVersionPapierCorrigee(These $these): Validation
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->validationService->findTypeValidationByCode(TypeValidation::CODE_VERSION_PAPIER_CORRIGEE),
            $these,
            $individu);

        $this->validationService->saveValidation($v);

        return $v;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function unvalidateDepotTheseCorrigee(These $these): Validation
    {
        $qb = $this->validationService->getRepository()->createQueryBuilder('v')
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
            $this->validationService->saveValidation($v);
        }

        return $v;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function validateCorrectionThese(These $these): Validation
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $v = new Validation(
            $this->validationService->findTypeValidationByCode(TypeValidation::CODE_CORRECTION_THESE),
            $these,
            $individu);

        $this->validationService->saveValidation($v);

        return $v;
    }

    /**
     * @param These $these
     * @return Validation
     */
    public function unvalidateCorrectionThese(These $these): Validation
    {
        $qb = $this->validationService->getRepository()->createQueryBuilder('v')
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

        $this->validationService->saveValidation($v);

        return $v;
    }

    /**
     * @param These $these
     * @return \Depot\Entity\Db\VSitu\DepotVersionCorrigeeValidationPresident[]
     */
    public function getValidationsAttenduesPourCorrectionThese(These $these): array
    {
        $repo = $this->validationService->getEntityManager()->getRepository(DepotVersionCorrigeeValidationPresident::class);
        $qb = $repo->createQueryBuilder('va')
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
            $this->validationService->findTypeValidationByCode(TypeValidation::CODE_PAGE_DE_COUVERTURE),
            $these,
            $individu);

        $this->validationService->saveValidation($v);
    }

    public function unvalidatePageDeCouverture($these)
    {
        $qb = $this->validationService->getRepository()->createQueryBuilder('v')
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

        $this->validationService->saveValidation($v);

        return $v;
    }
}