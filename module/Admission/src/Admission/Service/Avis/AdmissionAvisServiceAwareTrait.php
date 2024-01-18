<?php

namespace Admission\Service\Avis;

trait AdmissionAvisServiceAwareTrait
{
    /**
     * @var AdmissionAvisService
     */
    protected AdmissionAvisService $admissionAvisService;

    /**
     * @param AdmissionAvisService $admissionAvisService
     */
    public function setAdmissionAvisService(AdmissionAvisService $admissionAvisService)
    {
        $this->admissionAvisService = $admissionAvisService;
    }
}