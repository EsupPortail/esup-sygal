<?php

namespace Soutenance\Service\Simulation;

trait SimulationServiceAwareTrait {

    /** @var SimulationService $simulationService */
    private $simulationService;

    /**
     * @return SimulationService
     */
    public function getSimulationService()
    {
        return $this->simulationService;
    }

    /**
     * @param mixed $simulationService
     * @return SimulationService
     */
    public function setSimulationService($simulationService)
    {
        $this->simulationService = $simulationService;
        return $this->simulationService;
    }
}