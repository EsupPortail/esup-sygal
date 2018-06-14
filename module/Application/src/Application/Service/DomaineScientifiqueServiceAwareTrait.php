<?php

namespace Application\Service;

trait DomaineScientifiqueServiceAwareTrait
{
    /**
     * @var DomaineScientifiqueService
     */
    protected $domaineScientifiqueService;

    /**
     * @return DomaineScientifiqueService
     */
    public function getDomaineScientifiqueService()
    {
        return $this->domaineScientifiqueService;
    }

    /**
     * @param DomaineScientifiqueService $domaineScientifiqueService
     * @return DomaineScientifiqueServiceAwareTrait
     */
    public function setDomaineScientifiqueService($domaineScientifiqueService)
    {
        $this->domaineScientifiqueService = $domaineScientifiqueService;
        return $this;
    }

}