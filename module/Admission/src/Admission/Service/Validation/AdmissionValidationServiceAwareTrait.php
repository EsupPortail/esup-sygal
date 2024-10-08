<?php

namespace Admission\Service\Validation;

trait AdmissionValidationServiceAwareTrait
{
    /**
     * @var AdmissionValidationService
     */
    protected AdmissionValidationService $admissionValidationService;

    /**
     * @param AdmissionValidationService $admissionValidationService
     */
    public function setAdmissionValidationService(AdmissionValidationService $admissionValidationService): void
    {
        $this->admissionValidationService = $admissionValidationService;
    }

    /**
     * @return AdmissionValidationService
     */
    public function getAdmissionValidationService(): AdmissionValidationService
    {
        return $this->admissionValidationService;
    }
}