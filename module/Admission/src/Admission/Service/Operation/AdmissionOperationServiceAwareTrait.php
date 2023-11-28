<?php

namespace Admission\Service\Operation;

trait AdmissionOperationServiceAwareTrait
{
    /**
     * @var AdmissionOperationService
     */
    protected AdmissionOperationService $admissionOperationService;

    /**
     * @param AdmissionOperationService $admissionOperationService
     */
    public function setAdmissionOperationService(AdmissionOperationService $admissionOperationService)
    {
        $this->admissionOperationService = $admissionOperationService;
    }
}