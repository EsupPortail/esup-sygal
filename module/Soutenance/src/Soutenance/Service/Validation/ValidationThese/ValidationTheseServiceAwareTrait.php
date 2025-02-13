<?php

namespace Soutenance\Service\Validation\ValidationThese;

trait ValidationTheseServiceAwareTrait {

    protected ValidationTheseService $validationTheseService;

    public function getValidationTheseService(): ValidationTheseService
    {
        return $this->validationTheseService;
    }

    public function setValidationTheseService(ValidationTheseService $validationTheseService): void
    {
        $this->validationTheseService = $validationTheseService;
    }
}