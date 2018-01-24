<?php

namespace Application\Service\Validation;

trait TypeValidationServiceAwareTrait
{
    /**
     * @var TypeValidationService
     */
    protected $typeValidationService;

    /**
     * @param TypeValidationService $typeValidationService
     */
    public function setValidationService(TypeValidationService $typeValidationService)
    {
        $this->typeValidationService = $typeValidationService;
    }
}