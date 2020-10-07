<?php

namespace Soutenance\Form\ChangementTitre;

trait ChangementTitreFormAwareTrait{

    /** @var ChangementTitreForm */
    private $changementTitreForm;

    /**
     * @return ChangementTitreForm
     */
    public function getChangementTitreForm()
    {
        return $this->changementTitreForm;
    }

    /**
     * @param ChangementTitreForm $changementTitreForm
     * @return ChangementTitreForm
     */
    public function setChangementTitreForm($changementTitreForm)
    {
        $this->changementTitreForm = $changementTitreForm;
        return $this->changementTitreForm;
    }
}