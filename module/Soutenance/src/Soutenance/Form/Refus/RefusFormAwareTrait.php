<?php

namespace Soutenance\Form\Refus;

trait RefusFormAwareTrait {

    /** @var RefusForm */
    private $refusForm;

    /**
     * @return RefusForm
     */
    public function getRefusForm()
    {
        return $this->refusForm;
    }

    /**
     * @param RefusForm $refusForm
     * @return RefusForm
     */
    public function setRefusForm($refusForm)
    {
        $this->refusForm = $refusForm;
        return $this->refusForm;
    }


}