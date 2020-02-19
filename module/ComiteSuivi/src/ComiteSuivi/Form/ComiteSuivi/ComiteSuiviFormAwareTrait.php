<?php

namespace ComiteSuivi\Form\ComiteSuivi;

trait ComiteSuiviFormAwareTrait {

    /** @var ComiteSuiviForm */
    private $comiteSuiviForm;

    /**
     * @return ComiteSuiviForm
     */
    public function getComiteSuiviForm()
    {
        return $this->comiteSuiviForm;
    }

    /**
     * @param ComiteSuiviForm $comiteSuiviForm
     * @return ComiteSuiviForm
     */
    public function setComiteSuiviForm($comiteSuiviForm)
    {
        $this->comiteSuiviForm = $comiteSuiviForm;
        return $this->comiteSuiviForm;
    }


}