<?php

namespace Fichier\Form;

trait FichierFormAwareTrait
{
    protected FichierForm $fichierForm;

    public function setFichierForm(FichierForm $fichierForm): void
    {
        $this->fichierForm = $fichierForm;
    }
}