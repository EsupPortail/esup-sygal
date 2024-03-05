<?php

namespace Admission\Form\Admission;

trait AdmissionFormAwareTrait {

    private AdmissionForm $admissionForm;

    public function getAdmissionForm(): AdmissionForm
    {
        return $this->admissionForm;
    }

    public function setAdmissionForm(AdmissionForm $admissionForm): void
    {
        $this->admissionForm = $admissionForm;
    }

}