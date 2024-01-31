<?php

namespace Admission\Form\ConventionFormationDoctorale;

trait ConventionFormationDoctoraleFormAwareTrait {

    private ConventionFormationDoctoraleForm $conventionFormationDoctoraleForm;

    public function getConventionFormationDoctoraleForm(): ConventionFormationDoctoraleForm
    {
        return $this->conventionFormationDoctoraleForm;
    }

    public function setConventionFormationDoctoraleForm(ConventionFormationDoctoraleForm $conventionFormationDoctoraleForm): void
    {
        $this->conventionFormationDoctoraleForm = $conventionFormationDoctoraleForm;
    }

}