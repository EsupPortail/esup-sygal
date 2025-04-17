<?php

namespace Formation\Form\Formation\FormationExterne;

trait FormationExterneFormAwareTrait {

    private FormationExterneForm $formationExterneForm;

    /**
     * @return FormationExterneForm
     */
    public function getFormationExterneForm(): FormationExterneForm
    {
        return $this->formationExterneForm;
    }

    /**
     * @param FormationExterneForm $formationExterneForm
     */
    public function setFormationExterneForm(FormationExterneForm $formationExterneForm): void
    {
        $this->formationExterneForm = $formationExterneForm;
    }
}