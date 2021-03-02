<?php

namespace ComiteSuivi\Form\ComiteSuivi;

trait RefusFormAwareTrait {

    /** @var RefusForm */
    private $refusForm;

    /**
     * @return RefusForm
     */
    public function getRefusForm() : RefusForm
    {
        return $this->refusForm;
    }

    /**
     * @param RefusForm $refusForm
     * @return RefusForm
     */
    public function setRefusForm(RefusForm $refusForm) : RefusForm
    {
        $this->refusForm = $refusForm;
        return $this->refusForm;
    }

}
