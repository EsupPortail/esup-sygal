<?php

namespace Validation\Service\ValidationFormationExterne;

trait ValidationFormationExterneServiceAwareTrait
{
    protected ValidationFormationExterneService $validationFormationExterneService;

    public function setValidationFormationExterneService(ValidationFormationExterneService $validationFormationExterneService): void
    {
        $this->validationFormationExterneService = $validationFormationExterneService;
    }

    public function getValidationFormationExterneService(): ValidationFormationExterneService
    {
        return $this->validationFormationExterneService;
    }
}