<?php

namespace Formation\Form\Seance;

trait SeanceFormAwareTrait {

    /** @var SeanceForm */
    private $seanceForm;

    /**
     * @return SeanceForm
     */
    public function getSeanceForm(): SeanceForm
    {
        return $this->seanceForm;
    }

    /**
     * @param SeanceForm $seanceForm
     * @return SeanceForm
     */
    public function setSeanceForm(SeanceForm $seanceForm): SeanceForm
    {
        $this->seanceForm = $seanceForm;
        return $this->seanceForm;
    }

}