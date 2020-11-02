<?php

namespace Soutenance\Form\DateRenduRapport;

trait DateRenduRapportFormAwareTrait {

    /** @var DateRenduRapportForm */
    private $dateRenduRapportForm;

    /**
     * @return DateRenduRapportForm
     */
    public function getDateRenduRapportForm()
    {
        return $this->dateRenduRapportForm;
    }

    /**
     * @param DateRenduRapportForm $dateRenduRapportForm
     * @return DateRenduRapportForm
     */
    public function setDateRenduRapportForm($dateRenduRapportForm)
    {
        $this->dateRenduRapportForm = $dateRenduRapportForm;
        return $this->dateRenduRapportForm;
    }
}