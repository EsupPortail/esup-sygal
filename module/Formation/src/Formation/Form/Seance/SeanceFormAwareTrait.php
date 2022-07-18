<?php

namespace Formation\Form\Seance;

trait SeanceFormAwareTrait {

    private SeanceForm $seanceForm;

    /**
     * @return SeanceForm
     */
    public function getSeanceForm(): SeanceForm
    {
        return $this->seanceForm;
    }

    /**
     * @param SeanceForm $seanceForm
     */
    public function setSeanceForm(SeanceForm $seanceForm): void
    {
        $this->seanceForm = $seanceForm;
    }

}