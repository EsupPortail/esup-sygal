<?php

namespace ComiteSuivi\Form\CompteRendu;

trait CompteRenduFormAwareTrait {

    /** @var CompteRenduForm */
    private $compteRenduForm;

    /**
     * @return CompteRenduForm
     */
    public function getCompteRenduForm()
    {
        return $this->compteRenduForm;
    }

    /**
     * @param CompteRenduForm $compteRenduForm
     * @return CompteRenduForm
     */
    public function setCompteRenduForm($compteRenduForm)
    {
        $this->compteRenduForm = $compteRenduForm;
        return $this->compteRenduForm;
    }

}