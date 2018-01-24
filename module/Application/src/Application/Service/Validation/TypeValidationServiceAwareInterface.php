<?php

namespace Application\Service\Validation;

interface TypeValidationServiceAwareInterface
{
    public function setValidationService(TypeValidationService $service);
}