<?php

namespace Application\Service;

trait AnomalieServiceAwareTrait
{
    /**
     * @var AnomalieService
     */
    protected $anomalieService;

    /**
     * @return AnomalieService
     */
    public function getAnomalieService()
    {
        return $this->anomalieService;
    }

    /**
     * @param AnomalieService $anomalieService
     * @return AnomalieServiceAwareTrait
     */
    public function setAnomalieService($anomalieService)
    {
        $this->anomalieService = $anomalieService;
        return $this;
    }


}