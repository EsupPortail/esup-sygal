<?php

namespace Validation\Service;

trait ValidationServiceAwareTrait
{
    protected ValidationService $validationService;

    public function setValidationService(ValidationService $validationService): void
    {
        $this->validationService = $validationService;
    }

    public function getValidationService(): ValidationService
    {
        return $this->validationService;
    }


}