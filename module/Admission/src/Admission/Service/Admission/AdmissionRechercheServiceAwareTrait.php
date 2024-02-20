<?php

namespace Admission\Service\Admission;

trait AdmissionRechercheServiceAwareTrait
{
    /**
     * @var AdmissionRechercheService
     */
    protected $admissionRechercheService;

    /**
     * @param AdmissionRechercheService $admissionRechercheService
     */
    public function setAdmissionRechercheService(AdmissionRechercheService $admissionRechercheService)
    {
        $this->admissionRechercheService = $admissionRechercheService;
    }
}