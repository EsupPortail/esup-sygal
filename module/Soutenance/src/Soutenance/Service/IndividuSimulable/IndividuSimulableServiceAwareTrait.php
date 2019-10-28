<?php

namespace Soutenance\Service\IndividuSimulable;

trait IndividuSimulableServiceAwareTrait {

    /** @var IndividuSimulableService */
    private $individuSimulableService;

    /**
     * @return IndividuSimulableService
     */
    public function getIndividuSimulableService()
    {
        return $this->individuSimulableService;
    }

    /**
     * @param IndividuSimulableService $individuSimulableService
     * @return IndividuSimulableService
     */
    public function setIndividuSimulableService($individuSimulableService)
    {
        $this->individuSimulableService = $individuSimulableService;
        return $this->individuSimulableService;
    }


}