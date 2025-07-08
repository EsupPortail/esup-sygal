<?php

namespace Formation\Service\Formation;

trait FormationServiceAwareTrait
{
    protected FormationService $formationService;

    public function getFormationService(): FormationService
    {
        return $this->formationService;
    }

    public function setFormationService(FormationService $formationService): void
    {
        $this->formationService = $formationService;
    }

}