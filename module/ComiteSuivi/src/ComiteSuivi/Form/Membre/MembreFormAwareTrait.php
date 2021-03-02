<?php

namespace ComiteSuivi\Form\Membre;

trait MembreFormAwareTrait {

    /** @var MembreForm */
    private $membreForm;

    /**
     * @return MembreForm
     */
    public function getMembreForm() : MembreForm
    {
        return $this->membreForm;
    }

    /**
     * @param MembreForm $membreForm
     * @return MembreForm
     */
    public function setMembreForm(MembreForm $membreForm) : MembreForm
    {
        $this->membreForm = $membreForm;
        return $this->membreForm;
    }
}