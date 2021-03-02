<?php

namespace ComiteSuivi\Form\CompteRendu;

trait CompteRenduFormAwareTrait {

    /** @var CompteRenduForm */
    private $compteRenduForm;

    /**
     * @return CompteRenduForm
     */
    public function getCompteRenduForm() : CompteRenduForm
    {
        return $this->compteRenduForm;
    }

    /**
     * @param CompteRenduForm $compteRenduForm
     * @return CompteRenduForm
     */
    public function setCompteRenduForm(CompteRenduForm $compteRenduForm) : CompteRenduForm
    {
        $this->compteRenduForm = $compteRenduForm;
        return $this->compteRenduForm;
    }

}