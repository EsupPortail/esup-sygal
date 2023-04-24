<?php

namespace Application\Entity\Db\Interfaces;

use Application\Entity\Db\TypeValidation;

trait TypeValidationAwareTrait
{
    protected ?TypeValidation $typeValidation = null;

    public function setTypeValidation(TypeValidation $typeValidation): self
    {
        $this->typeValidation = $typeValidation;
        return $this;
    }
}