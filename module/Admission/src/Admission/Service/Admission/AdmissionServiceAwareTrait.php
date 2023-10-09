<?php

namespace Admission\Service\Admission;

trait AdmissionServiceAwareTrait
{
    /**
     * @var AdmissionService
     */
    protected $diplomeService;

    /**
     * @param AdmissionService $diplomeService
     */
    public function setDiplomeService(AdmissionService $diplomeService): void
    {
        $this->diplomeService = $diplomeService;
    }

    /**
     * @return AdmissionService
     */
    public function getDiplomeService(): AdmissionService
    {
        return $this->diplomeService;
    }
}