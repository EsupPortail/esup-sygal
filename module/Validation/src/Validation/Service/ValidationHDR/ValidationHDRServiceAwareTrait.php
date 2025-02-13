<?php

namespace Validation\Service\ValidationHDR;

use Validation\Service\ValidationHDR\ValidationHDRService;

trait ValidationHDRServiceAwareTrait
{
    protected ValidationHDRService $validationHDRService;

    public function setValidationHDRService(ValidationHDRService $validationHDRService): void
    {
        $this->validationHDRService = $validationHDRService;
    }

    public function getValidationHDRService(): ValidationHDRService
    {
        return $this->validationHDRService;
    }
}