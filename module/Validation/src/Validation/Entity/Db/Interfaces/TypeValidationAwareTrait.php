<?php

namespace Validation\Entity\Db\Interfaces;

use Validation\Entity\Db\TypeValidation;

trait TypeValidationAwareTrait
{
    protected ?TypeValidation $typeValidation = null;

    public function setTypeValidation(TypeValidation $typeValidation): self
    {
        $this->typeValidation = $typeValidation;
        return $this;
    }
}