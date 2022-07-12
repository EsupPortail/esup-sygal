<?php

namespace Formation\Form\Formation;

trait FormationFormAwareTrait {

    private FormationForm $formationForm;

    /**
     * @return FormationForm
     */
    public function getFormationForm(): FormationForm
    {
        return $this->formationForm;
    }

    /**
     * @param FormationForm $formationForm
     */
    public function setFormationForm(FormationForm $formationForm): void
    {
        $this->formationForm = $formationForm;
    }
}