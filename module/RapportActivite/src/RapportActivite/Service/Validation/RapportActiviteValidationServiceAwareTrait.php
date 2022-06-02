<?php

namespace RapportActivite\Service\Validation;

trait RapportActiviteValidationServiceAwareTrait
{
    /**
     * @var RapportActiviteValidationService
     */
    protected RapportActiviteValidationService $rapportActiviteValidationService;

    /**
     * @param RapportActiviteValidationService $rapportActiviteValidationService
     */
    public function setRapportActiviteValidationService(RapportActiviteValidationService $rapportActiviteValidationService)
    {
        $this->rapportActiviteValidationService = $rapportActiviteValidationService;
    }
}