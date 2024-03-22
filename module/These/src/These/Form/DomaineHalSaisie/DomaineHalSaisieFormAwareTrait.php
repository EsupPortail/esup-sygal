<?php

namespace These\Form\DomaineHalSaisie;

trait DomaineHalSaisieFormAwareTrait
{

    private DomaineHalSaisieForm $domaineHalSaisieForm;

    public function getDomaineHalSaisieForm(): DomaineHalSaisieForm
    {
        return $this->domaineHalSaisieForm;
    }

    public function setDomaineHalSaisieForm(DomaineHalSaisieForm $domaineHalSaisieForm): void
    {
        $this->domaineHalSaisieForm = $domaineHalSaisieForm;
    }


}