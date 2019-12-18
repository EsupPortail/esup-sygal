<?php

namespace Application\Form;

trait InitCompteFormAwareTrait {

    /** @var InitCompteForm */
    private $initCompteForm;

    /**
     * @return InitCompteForm
     */
    public function getInitCompteForm()
    {
        return $this->initCompteForm;
    }

    /**
     * @param InitCompteForm $initCompteForm
     * @return InitCompteForm
     */
    public function setInitCompteForm($initCompteForm)
    {
        $this->initCompteForm = $initCompteForm;
        return $this->initCompteForm;
    }


}
