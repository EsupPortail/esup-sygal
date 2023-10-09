<?php

namespace Admission\Form\Etudiant;

trait EtudiantFormAwareTrait {

    private EtudiantForm $etudiantForm;

    public function getEtudiantForm(): EtudiantForm
    {
        return $this->etudiantForm;
    }

    public function setEtudiantForm(EtudiantForm $etudiantForm): void
    {
        $this->etudiantForm = $etudiantForm;
    }

}