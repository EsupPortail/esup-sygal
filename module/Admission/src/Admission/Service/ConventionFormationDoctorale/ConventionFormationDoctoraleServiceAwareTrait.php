<?php

namespace Admission\Service\ConventionFormationDoctorale;

trait ConventionFormationDoctoraleServiceAwareTrait
{
    /**
     * @var ConventionFormationDoctoraleService
     */
    protected $conventionFormationDoctoraleService;

    /**
     * @param ConventionFormationDoctoraleService $conventionFormationDoctoraleService
     */
    public function setConventionFormationDoctoraleService(ConventionFormationDoctoraleService $conventionFormationDoctoraleService): void
    {
        $this->conventionFormationDoctoraleService = $conventionFormationDoctoraleService;
    }

    /**
     * @return ConventionFormationDoctoraleService
     */
    public function getConventionFormationDoctoraleService(): ConventionFormationDoctoraleService
    {
        return $this->conventionFormationDoctoraleService;
    }
}