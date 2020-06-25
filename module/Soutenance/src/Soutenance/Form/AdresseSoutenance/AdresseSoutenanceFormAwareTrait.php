<?php

namespace Soutenance\Form\AdresseSoutenance;

trait AdresseSoutenanceFormAwareTrait
{

    /** @var AdresseSoutenanceForm */
    private $adresseSoutenanceForm;

    /**
     * @return AdresseSoutenanceForm
     */
    public function getAdresseSoutenanceForm()
    {
        return $this->adresseSoutenanceForm;
    }

    /**
     * @param AdresseSoutenanceForm $adresseSoutenanceForm
     * @return AdresseSoutenanceForm
     */
    public function setAdresseSoutenanceForm($adresseSoutenanceForm)
    {
        $this->adresseSoutenanceForm = $adresseSoutenanceForm;
        return $this->adresseSoutenanceForm;
    }
}