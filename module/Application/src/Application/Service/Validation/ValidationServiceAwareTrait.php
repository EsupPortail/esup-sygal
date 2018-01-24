<?php

namespace Application\Service\Validation;

trait ValidationServiceAwareTrait
{
    /**
     * @var ValidationService
     */
    protected $validationService;

    /**
     * @param ValidationService $validationService
     */
    public function setValidationService(ValidationService $validationService)
    {
        $this->validationService = $validationService;
    }
}