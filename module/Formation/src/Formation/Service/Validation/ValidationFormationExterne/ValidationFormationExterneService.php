<?php

namespace Formation\Service\Validation\ValidationFormationExterne;

use Application\Service\UserContextServiceAwareTrait;
use Formation\Entity\Db\FormationExterne;
use Individu\Entity\Db\Individu;
use Individu\Service\IndividuServiceAwareTrait;
use UnicaenApp\Service\EntityManagerAwareTrait;
use Validation\Entity\Db\Repository\ValidationFormationExterneRepository;
use Validation\Entity\Db\Validation;
use Validation\Entity\Db\ValidationFormationExterne;
use Validation\Service\ValidationFormationExterne\ValidationFormationExterneServiceAwareTrait;
use Validation\Service\ValidationServiceAwareTrait;

class ValidationFormationExterneService
{
    use EntityManagerAwareTrait;
    use UserContextServiceAwareTrait;
    use IndividuServiceAwareTrait;
    use ValidationServiceAwareTrait;
    use ValidationFormationExterneServiceAwareTrait;

    public function getRepository(): ValidationFormationExterneRepository
    {
        return $this->validationFormationExterneService->getRepository();
    }

    public function create(string $type, FormationExterne $formationExterne, Individu $individu): ValidationFormationExterne
    {
        $typeValidation = $this->validationService->findTypeValidationByCode($type);
        $v = new ValidationFormationExterne(new Validation($typeValidation), $formationExterne, $individu);
        $this->validationFormationExterneService->saveValidation($v);

        return $v;
    }

    public function historiser(ValidationFormationExterne $validation): ValidationFormationExterne
    {
        return $this->validationFormationExterneService->historiser($validation);
    }
}