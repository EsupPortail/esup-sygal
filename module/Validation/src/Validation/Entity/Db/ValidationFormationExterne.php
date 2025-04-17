<?php

namespace Validation\Entity\Db;

use Formation\Entity\Db\FormationExterne;
use Individu\Entity\Db\Individu;

class ValidationFormationExterne extends AbstractValidationEntity
{
    private ?FormationExterne $formationExterne = null;

    public function __construct(Validation $validation, FormationExterne|null $formationExterne = null, Individu|null $individu = null)
    {
        parent::__construct($validation, $individu);

        $this->setFormationExterne($formationExterne);
    }

    public function setFormationExterne(FormationExterne $formationExterne = null): static
    {
        $this->formationExterne = $formationExterne;

        return $this;
    }

    public function getFormationExterne(): FormationExterne
    {
        return $this->formationExterne;
    }

    public function getResourceId(): string
    {
        return 'ValidationFormationExterne';
    }
}
