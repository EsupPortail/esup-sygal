<?php

namespace ComiteSuiviIndividuel\Form\Membre;

trait MembreFromAwareTrait {

    /** @var MembreForm */
    private $membreForm;

    /**
     * @return MembreForm
     */
    public function getMembreForm()
    {
        return $this->membreForm;
    }

    /**
     * @param MembreForm $membreForm
     * @return MembreForm
     */
    public function setMembreForm($membreForm)
    {
        $this->membreForm = $membreForm;
        return $this->membreForm;
    }


}