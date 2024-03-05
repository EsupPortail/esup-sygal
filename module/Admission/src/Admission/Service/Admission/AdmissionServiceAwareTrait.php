<?php

namespace Admission\Service\Admission;

trait AdmissionServiceAwareTrait
{
    /**
     * @var AdmissionService
     */
    protected $admissionService;

    /**
     * @param AdmissionService $admissionService
     */
    public function setAdmissionService(AdmissionService $admissionService): void
    {
        $this->admissionService = $admissionService;
    }

    /**
     * @return AdmissionService
     */
    public function getAdmissionService(): AdmissionService
    {
        return $this->admissionService;
    }
}