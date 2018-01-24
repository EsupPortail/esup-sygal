<?php

namespace Application\Service\Validation;

interface ValidationServiceAwareInterface
{
    public function setValidationService(ValidationService $service);
}