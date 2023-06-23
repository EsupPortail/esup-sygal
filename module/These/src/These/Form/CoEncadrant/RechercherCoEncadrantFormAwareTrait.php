<?php

namespace These\Form\CoEncadrant;

trait RechercherCoEncadrantFormAwareTrait
{

    private RechercherCoEncadrantForm $rechercherCoEncadrantForm;

    public function getRechercherCoEncadrantForm(): RechercherCoEncadrantForm
    {
        return $this->rechercherCoEncadrantForm;
    }

    public function setRechercherCoEncadrantForm(RechercherCoEncadrantForm $rechercherCoEncadrantForm): void
    {
        $this->rechercherCoEncadrantForm = $rechercherCoEncadrantForm;
    }


}