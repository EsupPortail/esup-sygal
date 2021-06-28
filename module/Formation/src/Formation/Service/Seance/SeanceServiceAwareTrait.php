<?php

namespace Formation\Service\Seance;

trait SeanceServiceAwareTrait
{
    /** @var SeanceService */
    private $seanceService;

    /**
     * @return SeanceService
     */
    public function getSeanceService(): SeanceService
    {
        return $this->seanceService;
    }

    /**
     * @param SeanceService $seanceService
     * @return SeanceService
     */
    public function setSeanceService(SeanceService $seanceService): SeanceService
    {
        $this->seanceService = $seanceService;
        return $this->seanceService;
    }

}