<?php

namespace Soutenance\Service\Intervention;

trait InterventionServiceAwareTrait {

    /** @var InterventionService */
    private $interventionService;

    /**
     * @return InterventionService
     */
    public function getInterventionService(): InterventionService
    {
        return $this->interventionService;
    }

    /**
     * @param InterventionService $interventionService
     * @return InterventionService
     */
    public function setInterventionService(InterventionService $interventionService): InterventionService
    {
        $this->interventionService = $interventionService;
        return $this->interventionService;
    }
}