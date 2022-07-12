<?php

namespace Formation\Service\Seance;

trait SeanceServiceAwareTrait
{
    private SeanceService $seanceService;

    /**
     * @return SeanceService
     */
    public function getSeanceService(): SeanceService
    {
        return $this->seanceService;
    }

    /**
     * @param SeanceService $seanceService
     */
    public function setSeanceService(SeanceService $seanceService): void
    {
        $this->seanceService = $seanceService;
    }

}