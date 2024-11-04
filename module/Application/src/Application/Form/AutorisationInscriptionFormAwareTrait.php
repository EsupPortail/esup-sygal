<?php

namespace Application\Form;

trait AutorisationInscriptionFormAwareTrait {

    /** @var AutorisationInscriptionForm */
    private $autorisationInscriptionForm;

    /**
     * @return AutorisationInscriptionForm
     */
    public function getAutorisationInscriptionForm()
    {
        return $this->autorisationInscriptionForm;
    }

    /**
     * @param AutorisationInscriptionForm $autorisationInscriptionForm
     * @return AutorisationInscriptionForm
     */
    public function setAutorisationInscriptionForm($autorisationInscriptionForm)
    {
        $this->autorisationInscriptionForm = $autorisationInscriptionForm;
        return $this->autorisationInscriptionForm;
    }
}
