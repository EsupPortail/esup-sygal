<?php

namespace Validation\Service\ValidationThese;

trait ValidationTheseServiceAwareTrait
{
    protected ValidationTheseService $validationTheseService;

    public function setValidationTheseService(ValidationTheseService $validationTheseService): void
    {
        $this->validationTheseService = $validationTheseService;
    }

    public function getValidationTheseService(): ValidationTheseService
    {
        return $this->validationTheseService;
    }
}