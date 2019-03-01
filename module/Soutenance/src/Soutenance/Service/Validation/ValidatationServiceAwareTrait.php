<?php

namespace Soutenance\Service\Validation;

trait ValidatationServiceAwareTrait {

    /** @var ValidationService */
    private $validationService;

    /**
     * @return ValidationService
     */
    public function getValidationService()
    {
        return $this->validationService;
    }

    /**
     * @param ValidationService $validationService
     * @return ValidationService
     */
    public function setValidationService($validationService)
    {
        $this->validationService = $validationService;
        return $this->validationService;
    }


}