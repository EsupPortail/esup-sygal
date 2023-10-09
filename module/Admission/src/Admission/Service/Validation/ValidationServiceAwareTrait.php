<?php

namespace Admission\Service\Validation;

trait ValidationServiceAwareTrait
{
    /**
     * @var ValidationService
     */
    protected ValidationService $validationService;

    /**
     * @param ValidationService $validationService
     */
    public function setValidationService(ValidationService $validationService): void
    {
        $this->validationService = $validationService;
    }

    /**
     * @return ValidationService
     */
    public function getValidationService(): ValidationService
    {
        return $this->validationService;
    }
}