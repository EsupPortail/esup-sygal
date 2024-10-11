<?php

namespace Admission\Form\Transmission;

trait TransmissionFormAwareTrait {

    private TransmissionForm $transmissionForm;

    public function getTransmissionForm(): TransmissionForm
    {
        return $this->transmissionForm;
    }

    public function setTransmissionForm(TransmissionForm $transmissionForm): void
    {
        $this->transmissionForm = $transmissionForm;
    }

}