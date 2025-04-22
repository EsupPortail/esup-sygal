<?php

namespace Formation\Service\Validation\ValidationFormationExterne;

trait ValidationFormationExterneServiceAwareTrait {

    protected ValidationFormationExterneService $validationFormationExterneService;

    public function getValidationFormationExterneService(): ValidationFormationExterneService
    {
        return $this->validationFormationExterneService;
    }

    public function setValidationFormationExterneService(ValidationFormationExterneService $validationFormationExterneService): void
    {
        $this->validationFormationExterneService = $validationFormationExterneService;
    }
}