<?php

namespace Soutenance\Service\Validation\ValidationHDR;

use Soutenance\Service\Validation\ValidationHDR\ValidationHDRService;

trait ValidationHDRServiceAwareTrait {

    protected ValidationHDRService $validationHDRService;

    public function getValidationHDRService(): ValidationHDRService
    {
        return $this->validationHDRService;
    }

    public function setValidationHDRService(ValidationHDRService $validationHDRService): void
    {
        $this->validationHDRService = $validationHDRService;
    }
}