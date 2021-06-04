<?php

namespace Application\Service\RapportValidation;

trait RapportValidationServiceAwareTrait
{
    /**
     * @var RapportValidationService
     */
    protected $rapportValidationService;

    /**
     * @param RapportValidationService $rapportValidationService
     */
    public function setRapportValidationService(RapportValidationService $rapportValidationService)
    {
        $this->rapportValidationService = $rapportValidationService;
    }
}