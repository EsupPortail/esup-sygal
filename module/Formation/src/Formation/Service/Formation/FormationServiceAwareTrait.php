<?php

namespace Formation\Service\Formation;

trait FormationServiceAwareTrait
{
    private FormationService $formationService;

    /**
     * @return FormationService
     */
    public function getFormationService(): FormationService
    {
        return $this->formationService;
    }

    /**
     * @param FormationService $formationService
     */
    public function setFormationService(FormationService $formationService): void
    {
        $this->formationService = $formationService;
    }

}