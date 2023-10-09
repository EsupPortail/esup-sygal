<?php

namespace Admission\Service\TypeValidation;

trait TypeValidationServiceAwareTrait
{
    /**
     * @var TypeValidationService
     */
    protected TypeValidationService $typeValidationService;

    /**
     * @param TypeValidationService $typeValidationService
     */
    public function setTypeValidationService(TypeValidationService $typeValidationService): void
    {
        $this->typeValidationService = $typeValidationService;
    }

    /**
     * @return TypeValidationService
     */
    public function getTypeValidationService(): TypeValidationService
    {
        return $this->typeValidationService;
    }
}