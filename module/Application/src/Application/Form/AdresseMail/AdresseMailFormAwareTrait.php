<?php

namespace Application\Form\AdresseMail;

trait AdresseMailFormAwareTrait {

    /** @var AdresseMailForm */
    private $adresseMailForm;

    /**
     * @return AdresseMailForm
     */
    public function getAdresseMailForm(): AdresseMailForm
    {
        return $this->adresseMailForm;
    }

    /**
     * @param AdresseMailForm $adresseMailForm
     * @return AdresseMailForm
     */
    public function setAdresseMailForm(AdresseMailForm $adresseMailForm): AdresseMailForm
    {
        $this->adresseMailForm = $adresseMailForm;
        return $this->adresseMailForm;
    }


}