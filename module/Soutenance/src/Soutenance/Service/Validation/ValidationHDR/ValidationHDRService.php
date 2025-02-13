<?php

namespace Soutenance\Service\Validation\ValidationHDR;

use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use HDR\Entity\Db\HDR;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Validation\Entity\Db\Repository\ValidationHDRRepository;
use Validation\Entity\Db\TypeValidation;
use Validation\Entity\Db\Validation;
use Validation\Entity\Db\ValidationHDR;
use Validation\Service\ValidationServiceAwareTrait;
use Validation\Service\ValidationHDR\ValidationHDRServiceAwareTrait;

class ValidationHDRService
{
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use ValidationHDRServiceAwareTrait;

    public function getRepository(): ValidationHDRRepository
    {
        return $this->validationHDRService->getRepository();
    }

    public function create(string $type, HDR $hdr, Individu $individu): ValidationHDR
    {
        $typeValidation = $this->validationService->findTypeValidationByCode($type);
        $v = new ValidationHDR(new Validation($typeValidation), $hdr, $individu);
        $this->validationHDRService->saveValidation($v);

        return $v;
    }

    public function historiser(ValidationHDR $validation): ValidationHDR
    {
        return $this->validationHDRService->historiser($validation);
    }

    public function validatePropositionSoutenance(HDR $hdr) : ValidationHDR
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();
        $v = $this->getRepository()->findValidationByHDRAndCodeAndIndividu($hdr, TypeValidation::CODE_PROPOSITION_SOUTENANCE, $individu);

        if ($v === null) {
            $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_PROPOSITION_SOUTENANCE);
            $v = new ValidationHDR(new Validation($typeValidation), $hdr, $individu);
            $this->validationHDRService->saveValidation($v);
        }

        return $v;
    }

    public function unvalidatePropositionSoutenance($hdr)
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->andWhereHDRIs($hdr)
            ->andWhereTypeIs($type = TypeValidation::CODE_PROPOSITION_SOUTENANCE)
            ->andWhereNotHistorise();
        /** @var ValidationHDR $v */
        try {
            $v = $qb->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            throw new RuntimeException(
                sprintf("Anomalie: plus d'une validation de type '%s' trouvée pour la thèse %s", $type, $hdr));
        }

        if (!$v) {
            throw new RuntimeException(
                sprintf("Aucune validation de type '%s' trouvée pour la thèse %s", $type, $hdr));
        }

        $v->historiser();
        $this->validationHDRService->saveValidation($v);

        return $v;
    }

    public function findValidationPropositionSoutenanceByHDRAndIndividu(HDR $hdr, Individu $individu): ValidationHDR|null
    {
        $qb = $this->getRepository()->createQueryBuilder("v")
            ->andWhereHDRIs($hdr)
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
     * @return ValidationHDR[]
     */
    public function findValidationPropositionSoutenanceByHDR(HDR $hdr): array
    {
        $qb = $this->getRepository()->createQueryBuilder("v")
            ->andWhereHDRIs($hdr)
            ->andWhereTypeIs($type = TypeValidation::CODE_PROPOSITION_SOUTENANCE)
            ->andWhereNotHistorise()
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @deprecated Utiliser {@see self::historiser()}
     */
    public function historise($validation): ValidationHDR
    {
        return $this->historiser($validation);
    }

    public function unsignEngagementImpartialite(ValidationHDR $validation): ValidationHDR
    {
        return $this->historiser($validation);
    }

    public function validateValidationUR($hdr, $individu): ValidationHDR
    {
        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_VALIDATION_PROPOSITION_UR);
        $v = new ValidationHDR(new Validation($typeValidation), $hdr, $individu);
        $this->validationHDRService->saveValidation($v);

        return $v;
    }

    public function validateValidationED($hdr, $individu): ValidationHDR
    {
        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_VALIDATION_PROPOSITION_ED);
        $v = new ValidationHDR(new Validation($typeValidation), $hdr, $individu);
        $this->validationHDRService->saveValidation($v);

        return $v;
    }

    public function validateValidationBDD($hdr, $individu): ValidationHDR
    {
        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD);
        $v = new ValidationHDR(new Validation($typeValidation), $hdr, $individu);
        $this->validationHDRService->saveValidation($v);

        return $v;
    }

    public function signerAvisSoutenance($hdr, $individu): ValidationHDR
    {
        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_AVIS_SOUTENANCE);
        $v = new ValidationHDR(new Validation($typeValidation), $hdr, $individu);
        $this->validationHDRService->saveValidation($v);

        return $v;
    }
}