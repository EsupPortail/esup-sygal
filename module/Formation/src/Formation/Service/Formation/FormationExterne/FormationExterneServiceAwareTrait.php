<?php

namespace Formation\Service\Formation\FormationExterne;


trait FormationExterneServiceAwareTrait
{
    private FormationExterneService $formationExterneService;

    /**
     * @return FormationExterneService
     */
    public function getFormationExterneService(): FormationExterneService
    {
        return $this->formationExterneService;
    }

    /**
     * @param FormationExterneService $formationExterneService
     */
    public function setFormationExterneService(FormationExterneService $formationExterneService): void
    {
        $this->formationExterneService = $formationExterneService;
    }

}