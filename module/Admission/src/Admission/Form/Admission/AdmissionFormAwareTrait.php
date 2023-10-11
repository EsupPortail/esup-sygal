<?php

namespace Admission\Form\Admission;

trait AdmissionFormAwareTrait {

    private AdmissionForm $etudiantForm;

    public function getEtudiantForm(): AdmissionForm
    {
        return $this->etudiantForm;
    }

    public function setEtudiantForm(AdmissionForm $etudiantForm): void
    {
        $this->etudiantForm = $etudiantForm;
    }

}