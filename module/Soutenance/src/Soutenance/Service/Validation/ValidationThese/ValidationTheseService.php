<?php

namespace Soutenance\Service\Validation\ValidationThese;

use Application\Service\UserContextServiceAwareTrait;
use Doctrine\ORM\NonUniqueResultException;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use These\Entity\Db\These;
use UnicaenApp\Exception\RuntimeException;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Validation\Entity\Db\Repository\ValidationTheseRepository;
use Validation\Entity\Db\TypeValidation;
use Validation\Entity\Db\Validation;
use Validation\Entity\Db\ValidationThese;
use Validation\Service\ValidationServiceAwareTrait;
use Validation\Service\ValidationThese\ValidationTheseServiceAwareTrait;

class ValidationTheseService
{
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use ValidationTheseServiceAwareTrait;

    public function getRepository(): ValidationTheseRepository
    {
        return $this->validationTheseService->getRepository();
    }

    public function create(string $type, These $these, Individu $individu): ValidationThese
    {
        $typeValidation = $this->validationService->findTypeValidationByCode($type);
        $v = new ValidationThese(new Validation($typeValidation), $these, $individu);
        $this->validationTheseService->saveValidation($v);

        return $v;
    }

    public function historiser(ValidationThese $validation): ValidationThese
    {
        return $this->validationTheseService->historiser($validation);
    }

    public function validatePropositionSoutenance(These $these) : ValidationThese
    {
        // l'individu sera enregistré dans la validation pour faire le lien entre Utilisateur et Individu.
        $individu = $this->userContextService->getIdentityIndividu();
        $v = $this->getRepository()->findValidationByTheseAndCodeAndIndividu($these, TypeValidation::CODE_PROPOSITION_SOUTENANCE, $individu);

        if ($v === null) {
            $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_PROPOSITION_SOUTENANCE);
            $v = new ValidationThese(new Validation($typeValidation), $these, $individu);
            $this->validationTheseService->saveValidation($v);
        }

        return $v;
    }

    public function unvalidatePropositionSoutenance($these)
    {
        $qb = $this->getRepository()->createQueryBuilder('v')
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_PROPOSITION_SOUTENANCE)
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

    public function findValidationPropositionSoutenanceByTheseAndIndividu(These $these, Individu $individu): ValidationThese|null
    {
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
     * @return ValidationThese[]
     */
    public function findValidationPropositionSoutenanceByThese(These $these): array
    {
        $qb = $this->getRepository()->createQueryBuilder("v")
            ->andWhereTheseIs($these)
            ->andWhereTypeIs($type = TypeValidation::CODE_PROPOSITION_SOUTENANCE)
            ->andWhereNotHistorise()
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @deprecated Utiliser {@see self::historiser()}
     */
    public function historise($validation): ValidationThese
    {
        return $this->historiser($validation);
    }

    public function unsignEngagementImpartialite(ValidationThese $validation): ValidationThese
    {
        return $this->historiser($validation);
    }

    public function validateValidationUR($these, $individu): ValidationThese
    {
        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_VALIDATION_PROPOSITION_UR);
        $v = new ValidationThese(new Validation($typeValidation), $these, $individu);
        $this->validationTheseService->saveValidation($v);

        return $v;
    }

    public function validateValidationED($these, $individu): ValidationThese
    {
        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_VALIDATION_PROPOSITION_ED);
        $v = new ValidationThese(new Validation($typeValidation), $these, $individu);
        $this->validationTheseService->saveValidation($v);

        return $v;
    }

    public function validateValidationBDD($these, $individu): ValidationThese
    {
        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_VALIDATION_PROPOSITION_BDD);
        $v = new ValidationThese(new Validation($typeValidation), $these, $individu);
        $this->validationTheseService->saveValidation($v);

        return $v;
    }

    public function signerAvisSoutenance($these, $individu): ValidationThese
    {
        $typeValidation = $this->validationService->findTypeValidationByCode(TypeValidation::CODE_AVIS_SOUTENANCE);
        $v = new ValidationThese(new Validation($typeValidation), $these, $individu);
        $this->validationTheseService->saveValidation($v);

        return $v;
    }
}