<?php

namespace Formation\Service\Formation;

trait FormationServiceAwareTrait
{
    /** @var FormationService */
    private $formationService;

    /**
     * @return FormationService
     */
    public function getFormationService(): FormationService
    {
        return $this->formationService;
    }

    /**
     * @param FormationService $formationService
     * @return FormationService
     */
    public function setFormationService(FormationService $formationService): FormationService
    {
        $this->formationService = $formationService;
        return $this->formationService;
    }

}