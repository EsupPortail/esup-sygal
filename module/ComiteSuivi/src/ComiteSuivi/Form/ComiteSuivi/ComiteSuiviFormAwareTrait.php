<?php

namespace ComiteSuivi\Form\ComiteSuivi;

trait ComiteSuiviFormAwareTrait {

    /** @var ComiteSuiviForm */
    private $comiteSuiviForm;

    /**
     * @return ComiteSuiviForm
     */
    public function getComiteSuiviForm() : ComiteSuiviForm
    {
        return $this->comiteSuiviForm;
    }

    /**
     * @param ComiteSuiviForm $comiteSuiviForm
     * @return ComiteSuiviForm
     */
    public function setComiteSuiviForm(ComiteSuiviForm $comiteSuiviForm) : ComiteSuiviForm
    {
        $this->comiteSuiviForm = $comiteSuiviForm;
        return $this->comiteSuiviForm;
    }


}