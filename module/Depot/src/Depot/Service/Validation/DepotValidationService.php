<?php

namespace Depot\Service\Validation;

use Application\Service\UserContextServiceAwareTrait;
use Depot\Entity\Db\VSitu\DepotVersionCorrigeeValidationPresident;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Individu\Entity\Db\Individu;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use Validation\Entity\Db\TypeValidation;
use Validation\Entity\Db\Validation;
use Validation\Entity\Db\ValidationThese;
use Validation\Service\ValidationThese\ValidationTheseServiceAwareTrait;
use Validation\Service\ValidationServiceAwareTrait;

class DepotValidationService
{
    use ValidationServiceAwareTrait;
    use ValidationTheseServiceAwareTrait;
    use UserContextServiceAwareTrait;

    public function getEntityManager(): EntityManager
    {
        return $this->validationService->getEntityManager();
    }

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
     * @return ValidationThese
     */
    public function validateRdvBu(These $these, Individu $createur): ValidationThese
    {
        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_RDV_BU);
        $validationThese = new ValidationThese(new Validation($typeValidation), $these, $createur);
        $this->validationTheseService->saveValidation($validationThese);

        return $validationThese;
    }

    /**
     * @param These $these
     */
    public function unvalidateRdvBu(These $these)
    {
        $qb = $this->validationTheseService->getRepository()->createQueryBuilder('v')
            ->andWhere('tv.code = :tvcode')
            ->andWhere('v.these = :these')
            ->andWhere('v.histoDestruction is null')
            ->setParameter('these', $these)
            ->setParameter('tvcode', TypeValidation::CODE_RDV_BU);
        /** @var ValidationThese $v */
        $v = $qb->getQuery()->getOneOrNullResult();

        if (!$v) {
            throw new RuntimeException(
                sprintf("Aucune validation de type '%s' trouvée pour la thèse %s", TypeValidation::CODE_RDV_BU, $these->getId()));
        }

        $v->historiser();

        $this->validationTheseService->saveValidation($v);
    }

    /**
     * @param These $these
     * @return ValidationThese
     */
    public function validateDepotTheseCorrigee(These $these): ValidationThese
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_DEPOT_THESE_CORRIGEE);
        $validationThese = new ValidationThese(new Validation($typeValidation), $these, $individu);
        $this->validationTheseService->saveValidation($validationThese);

        return $validationThese;
    }

    /**
     * @param These $these
     * @return ValidationThese
     */
    public function validateVersionPapierCorrigee(These $these): ValidationThese
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_VERSION_PAPIER_CORRIGEE);
        $validationThese = new ValidationThese(new Validation($typeValidation), $these, $individu);
        $this->validationTheseService->saveValidation($validationThese);

        return $validationThese;
    }

    /**
     * @param These $these
     * @return ValidationThese
     */
    public function unvalidateDepotTheseCorrigee(These $these): ValidationThese
    {
        $qb = $this->validationTheseService->getRepository()->createQueryBuilder('v')
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_DEPOT_THESE_CORRIGEE)
            ->andWhereNotHistorise();
        try {
            /** @var ValidationThese $v */
            $v = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(
                sprintf("Plusieures validations non historisées de type '%s' trouvées pour la thèse '%s'", $type, $these));
        }

        if ($v !== null) {
            $v->historiser();
            $this->validationTheseService->saveValidation($v);
        }

        return $v;
    }

    /**
     * @param These $these
     * @return ValidationThese
     */
    public function validateCorrectionThese(These $these): ValidationThese
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_CORRECTION_THESE);
        $validationThese = new ValidationThese(new Validation($typeValidation), $these, $individu);
        $this->validationTheseService->saveValidation($validationThese);

        return $validationThese;
    }

    /**
     * @param These $these
     * @return ValidationThese
     */
    public function unvalidateCorrectionThese(These $these): ValidationThese
    {
        $qb = $this->validationTheseService->getRepository()->createQueryBuilder('v')
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_CORRECTION_THESE)
            ->andWhereNotHistorise();
        /** @var ValidationThese $v */
        $v = $qb->getQuery()->getOneOrNullResult();

        if (!$v) {
            throw new RuntimeException(
                sprintf("Aucune validation de type '%s' trouvée pour la thèse %s", $type, $these));
        }

        $v->historiser();

        $this->validationTheseService->saveValidation($v);

        return $v;
    }

    /**
     * @return \Depot\Entity\Db\VSitu\DepotVersionCorrigeeValidationPresident[]
     */
    public function getValidationsAttenduesPourCorrectionThese(These $these): array
    {
        $repo = $this->validationTheseService->getEntityManager()->getRepository(DepotVersionCorrigeeValidationPresident::class);
        $qb = $repo->createQueryBuilder('va')
            ->addSelect('t, i')
            ->join('va.these', 't', Join::WITH, 't = :these')
            ->join('va.individu', 'i')
            ->andWhere('va.valide = 0')
            ->setParameter('these', $these);

        return $qb->getQuery()->getResult();
    }

    public function validatePageDeCouverture($these): ValidationThese
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();

        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_PAGE_DE_COUVERTURE);
        $validationThese = new ValidationThese(new Validation($typeValidation), $these, $individu);
        $this->validationTheseService->saveValidation($validationThese);

        return $validationThese;
    }

    public function unvalidatePageDeCouverture($these)
    {
        $qb = $this->validationTheseService->getRepository()->createQueryBuilder('v')
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_PAGE_DE_COUVERTURE)
            ->andWhereNotHistorise();
        /** @var ValidationThese $v */
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

        $this->validationTheseService->saveValidation($v);

        return $v;
    }
}